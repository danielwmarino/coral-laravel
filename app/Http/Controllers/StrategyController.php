<?php

namespace App\Http\Controllers;

use App\Models\Strategy;

class StrategyController extends Controller
{
    private const BLACK = 'FF000000';
    private const WHITE = 'FFFFFFFF';
    private const GRAY  = 'FF666666';

    public function downloadSlides(string $id)
    {
        $strategy = Strategy::findOrFail($id);

        $user = auth()->user();
        if ($user->isClientUser()) {
            $client = $user->profile?->client;
        } else {
            $clientId = session('active_client_id');
            $client = $clientId ? \App\Models\Client::find($clientId) : null;
        }

        if (!$client || $strategy->client_id !== $client->id) {
            abort(403);
        }

        try {
            $slides = $this->parseSlides($strategy->generated_document ?? '', $client->name);

            $prs = new \PhpOffice\PhpPresentation\PhpPresentation();
            $prs->getDocumentProperties()->setTitle("{$client->name} Strategy");

            foreach ($slides as $i => $slideData) {
                // Reuse the default first slide rather than removing it
                $slide = ($i === 0) ? $prs->getActiveSlide() : $prs->createSlide();
                $this->buildSlide($slide, $slideData);
            }

            $path = sys_get_temp_dir() . '/coral_' . uniqid() . '.pptx';
            \PhpOffice\PhpPresentation\IOFactory::createWriter($prs, 'PowerPoint2007')->save($path);

            $filename = \Illuminate\Support\Str::slug($client->name . ' Strategy') . '.pptx';
            return response()->download($path, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            ])->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('PPTX error', ['msg' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
            abort(500, $e->getMessage());
        }
    }

    private function parseSlides(string $doc, string $clientName): array
    {
        try {
            $prompt = "Convert this marketing strategy document into a slide deck for {$clientName}. "
                . "Return a JSON array of slides. Each slide: {\"type\":\"title\" or \"content\", \"title\":\"...\", \"bullets\":[\"...\",\"...\"]}. "
                . "First slide is type title with client name and one subtitle bullet. "
                . "Create 8-12 content slides covering key sections. 3-5 bullets per slide, each bullet max 12 words. "
                . "Return ONLY valid JSON array, no markdown fences.\n\nDOCUMENT:\n" . mb_substr($doc, 0, 5000);

            $response = app(\Anthropic\Client::class)->messages->create(
                maxTokens: 2000,
                messages: [['role' => 'user', 'content' => $prompt]],
                model: 'claude-haiku-4-5-20251001',
            );
            $json   = trim($response->content[0]->text ?? '');
            $json   = preg_replace('/^```(?:json)?\s*/m', '', $json);
            $json   = preg_replace('/\s*```\s*$/m', '', $json);
            $slides = json_decode(trim($json), true);
            if (is_array($slides) && count($slides) >= 2) {
                return $slides;
            }
        } catch (\Throwable $e) {
            // Fall through to basic parse
        }
        return $this->basicParse($doc, $clientName);
    }

    private function basicParse(string $doc, string $clientName): array
    {
        $slides = [
            ['type' => 'title', 'title' => "{$clientName} Marketing Strategy", 'bullets' => ['Powered by Coral Intelligence']],
        ];

        $sections = preg_split('/^#{1,3}\s+/m', $doc, -1, PREG_SPLIT_NO_EMPTY);
        foreach (array_slice($sections, 0, 10) as $section) {
            $lines = array_values(array_filter(array_map('trim', explode("\n", trim($section)))));
            if (empty($lines)) continue;
            $title   = $lines[0];
            $bullets = [];
            foreach (array_slice($lines, 1) as $line) {
                $line = preg_replace('/^[-*•]\s*/', '', $line);
                if ($line && strlen($line) > 5) $bullets[] = $line;
                if (count($bullets) >= 5) break;
            }
            if ($bullets) {
                $slides[] = ['type' => 'content', 'title' => $title, 'bullets' => $bullets];
            }
        }
        return $slides;
    }

    private function buildSlide($slide, array $data): void
    {
        $isTitle = ($data['type'] ?? 'content') === 'title';
        $title   = strval($data['title'] ?? '');
        $bullets = array_values(array_filter(array_map('strval', $data['bullets'] ?? [])));

        // Slide is 960×540 px
        if ($isTitle) {
            $t = $slide->createRichTextShape();
            $t->setOffsetX(60)->setOffsetY(180)->setWidth(840)->setHeight(150);
            $run = $t->createTextRun($title);
            $run->getFont()->setBold(true)->setSize(36);
            $t->getActiveParagraph()->getAlignment()->setHorizontal('ctr');

            if (!empty($bullets)) {
                $s = $slide->createRichTextShape();
                $s->setOffsetX(60)->setOffsetY(350)->setWidth(840)->setHeight(60);
                $s->createTextRun(implode('  ·  ', $bullets));
                $s->getActiveParagraph()->getAlignment()->setHorizontal('ctr');
            }
        } else {
            $t = $slide->createRichTextShape();
            $t->setOffsetX(50)->setOffsetY(30)->setWidth(860)->setHeight(70);
            $run = $t->createTextRun($title);
            $run->getFont()->setBold(true)->setSize(28);

            $yStart = 120;
            $yStep  = 65;
            foreach (array_slice($bullets, 0, 6) as $i => $bullet) {
                $b = $slide->createRichTextShape();
                $b->setOffsetX(50)->setOffsetY($yStart + $i * $yStep)->setWidth(860)->setHeight(60);
                $b->createTextRun('•  ' . $bullet);
            }
        }
    }

}
