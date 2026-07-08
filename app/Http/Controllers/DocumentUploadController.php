<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\KnowledgeChunk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class DocumentUploadController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'document_file'  => 'required|file|mimes:pdf,txt,doc,docx|max:20480',
            'document_label' => 'required|string|max:255',
            'client_id'      => 'required|uuid',
        ]);

        $user = auth()->user();
        $clientId = $request->input('client_id');

        // Verify the client belongs to this user's scope
        if ($user->isClientUser()) {
            $client = $user->profile?->client;
            abort_if(!$client || $client->id !== $clientId, 403);
        } else {
            $client = Client::find($clientId);
            abort_if(!$client, 403);
        }

        $file  = $request->file('document_file');
        $label = $request->input('document_label');
        $ext   = strtolower($file->getClientOriginalExtension());
        $path  = $file->getRealPath();

        $text = match ($ext) {
            'txt' => file_get_contents($path),
            'pdf' => $this->extractPdf($path),
            default => $this->extractDocx($path),
        };

        $chunks = $this->chunkText($text, 1000);

        // Replace existing chunks for this label
        KnowledgeChunk::where('client_id', $client->id)
            ->where('source_label', $label)
            ->where('source_type', 'document')
            ->delete();

        foreach ($chunks as $chunk) {
            KnowledgeChunk::create([
                'client_id'    => $client->id,
                'source_type'  => 'document',
                'source_label' => $label,
                'chunk_text'   => $chunk,
            ]);
        }

        return redirect()->route('dataset', ['tab' => 'documents'])
            ->with('toast', 'Document added to knowledge base (' . count($chunks) . ' chunks)');
    }

    private function extractPdf(string $path): string
    {
        try {
            $parser = new \Smalot\PdfParser\Parser();
            $text   = $parser->parseFile($path)->getText();
        } catch (\Exception) {
            $text = '';
        }
        return $text ?: '[PDF content could not be extracted — try uploading a text-based PDF]';
    }

    private function extractDocx(string $path): string
    {
        $zip = new \ZipArchive();
        if ($zip->open($path) === true) {
            $xml = $zip->getFromName('word/document.xml');
            $zip->close();
            return $xml ? strip_tags($xml) : '[Could not extract text from document]';
        }
        return '[Could not open document]';
    }

    private function chunkText(string $text, int $size): array
    {
        $text = preg_replace('/\s+/', ' ', trim($text));
        if (strlen($text) <= $size) return $text ? [$text] : [];

        $chunks  = [];
        $words   = explode(' ', $text);
        $current = '';
        foreach ($words as $word) {
            if (strlen($current) + strlen($word) + 1 > $size && $current !== '') {
                $chunks[]  = trim($current);
                $current   = $word;
            } else {
                $current .= ($current ? ' ' : '') . $word;
            }
        }
        if (trim($current)) $chunks[] = trim($current);
        return $chunks;
    }
}
