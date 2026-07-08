<?php

namespace App\Services;

class AuditChecklist
{
    public static function uxItems(): array
    {
        return [
            'First Impressions & Clarity' => [
                ['key' => 'first_value_prop',      'text' => 'The value proposition is clear within 5 seconds of landing'],
                ['key' => 'first_cta_visible',     'text' => 'The primary action/CTA is immediately visible without scrolling'],
                ['key' => 'first_target_audience', 'text' => 'The target audience can self-identify within moments of landing'],
                ['key' => 'first_hero_message',    'text' => 'Brand hierarchy guides the eye to the most important message first'],
                ['key' => 'first_purpose_obvious', 'text' => "The product's purpose is obvious to a first-time visitor"],
                ['key' => 'first_load_speed',      'text' => 'The page/screen loads in a perceived "instant" — no blank states or layout shift'],
                ['key' => 'first_brand_consistent','text' => 'Brand is consistent and professional'],
                ['key' => 'first_differentiation', 'text' => "There's enough language that differentiates it from a generic competitor"],
            ],
            'Navigation & Information Architecture' => [
                ['key' => 'nav_findable',       'text' => 'Primary navigation is easy to find and clearly labelled'],
                ['key' => 'nav_wayfinding',     'text' => 'Users can always tell where they are in the product'],
                ['key' => 'nav_clear_map',      'text' => 'There is a clear map to every major area of the product'],
                ['key' => 'nav_back',           'text' => 'There is a clear way to go back or undo actions'],
                ['key' => 'nav_search',         'text' => 'Search is available when content volume justifies it'],
                ['key' => 'nav_labels',         'text' => 'Navigation labels use words the target audience would use'],
                ['key' => 'nav_depth',          'text' => 'Navigation depth doesn\'t require more than 3 clicks to reach core content'],
                ['key' => 'nav_mobile_friendly','text' => 'Navigation collapses or adapts correctly on mobile'],
            ],
            'Forms & Input UX' => [
                ['key' => 'forms_labels',          'text' => 'All form fields have clear labels (not just placeholders)'],
                ['key' => 'forms_errors',          'text' => 'Error messages are specific and tell the user what to fix'],
                ['key' => 'forms_required',        'text' => 'Required fields are marked clearly'],
                ['key' => 'forms_validation',      'text' => 'Inline validation happens before form submission where possible'],
                ['key' => 'forms_progress',        'text' => 'Multi-step forms show progress'],
                ['key' => 'forms_autofill',        'text' => 'Forms support browser autofill'],
                ['key' => 'forms_submit_feedback', 'text' => 'Submitting a form gives clear confirmation feedback'],
                ['key' => 'forms_field_length',    'text' => 'Input fields are sized appropriately to expected content'],
            ],
            'Feedback & System Status' => [
                ['key' => 'feedback_loading',      'text' => 'Loading states are always communicated (spinner, skeleton, progress)'],
                ['key' => 'feedback_success',      'text' => 'Success states are clearly communicated'],
                ['key' => 'feedback_errors',       'text' => 'Error states are clearly communicated with recovery paths'],
                ['key' => 'feedback_empty',        'text' => 'Empty states are designed and informative (not just blank)'],
                ['key' => 'feedback_notifications','text' => 'Notifications and alerts are timely and dismissible'],
                ['key' => 'feedback_destructive',  'text' => 'Destructive actions require confirmation'],
                ['key' => 'feedback_offline',      'text' => 'The product gracefully handles offline or slow connections'],
                ['key' => 'feedback_realtime',     'text' => 'Real-time changes are reflected without requiring a page reload'],
            ],
            'Accessibility & Inclusivity' => [
                ['key' => 'a11y_contrast',    'text' => 'Text contrast meets WCAG AA standards'],
                ['key' => 'a11y_keyboard',    'text' => 'All interactive elements are keyboard accessible'],
                ['key' => 'a11y_focus',       'text' => 'Focus states are visible on all interactive elements'],
                ['key' => 'a11y_alt_text',    'text' => 'Images have meaningful alt text'],
                ['key' => 'a11y_aria',        'text' => 'ARIA roles and labels are used appropriately'],
                ['key' => 'a11y_font_size',   'text' => 'Base font size is readable (16px+) without zooming'],
                ['key' => 'a11y_colour_only', 'text' => 'Information is not conveyed by colour alone'],
                ['key' => 'a11y_motion',      'text' => 'Animations respect prefers-reduced-motion'],
            ],
            'Mobile & Responsive Experience' => [
                ['key' => 'mobile_layout',       'text' => 'Layout adapts correctly to mobile screen sizes'],
                ['key' => 'mobile_touch_targets','text' => 'Touch targets are at least 44x44px'],
                ['key' => 'mobile_text_size',    'text' => 'Text is readable without zooming on mobile'],
                ['key' => 'mobile_no_overflow',  'text' => 'No horizontal scrolling occurs on mobile'],
                ['key' => 'mobile_images',       'text' => 'Images and media scale correctly on mobile'],
                ['key' => 'mobile_forms',        'text' => 'Forms are usable on mobile keyboards'],
                ['key' => 'mobile_performance',  'text' => 'Mobile performance is acceptable on 4G'],
                ['key' => 'mobile_gestures',     'text' => 'Swipe and native gestures are used appropriately'],
            ],
            'Trust, Credibility & Conversion' => [
                ['key' => 'trust_social_proof', 'text' => 'Social proof is present (testimonials, logos, reviews, case studies)'],
                ['key' => 'trust_contact',      'text' => 'Contact information is easy to find'],
                ['key' => 'trust_security',     'text' => 'Security signals are visible where relevant (SSL, badges, privacy)'],
                ['key' => 'trust_about',        'text' => 'There is a credible About or Team page'],
                ['key' => 'trust_cta_friction', 'text' => 'The primary CTA has minimal friction (no unnecessary fields)'],
                ['key' => 'trust_pricing_clear','text' => 'Pricing is clear or expectations are set around cost'],
                ['key' => 'trust_refund',       'text' => 'Return/refund/cancellation policies are easy to find if relevant'],
                ['key' => 'trust_consistency',  'text' => 'Visual and copy consistency builds trust throughout'],
            ],
        ];
    }

    public static function contentItems(): array
    {
        return [
            'Messaging & Value Proposition' => [
                ['key' => 'msg_headline',             'text' => 'The headline communicates what the product/service does, not just a tagline'],
                ['key' => 'msg_subheadline',          'text' => 'The subheadline adds specificity or addresses the primary pain point'],
                ['key' => 'msg_audience',             'text' => "Messaging speaks directly to the target audience's language"],
                ['key' => 'msg_benefits_over_features','text' => 'Benefits are emphasised over features'],
                ['key' => 'msg_differentiation',      'text' => 'The copy clearly differentiates from competitors'],
                ['key' => 'msg_tone_consistent',      'text' => 'Tone of voice is consistent across all pages'],
                ['key' => 'msg_jargon',               'text' => 'Content avoids unnecessary jargon for the audience'],
            ],
            'Calls to Action' => [
                ['key' => 'cta_present',        'text' => 'Every key page has a clear primary CTA'],
                ['key' => 'cta_action_language','text' => 'CTA copy uses action-oriented language (not just "Submit" or "Click Here")'],
                ['key' => 'cta_above_fold',     'text' => 'The primary CTA is visible above the fold on key pages'],
                ['key' => 'cta_repeated',       'text' => 'Long pages repeat CTAs at logical intervals'],
                ['key' => 'cta_single_primary', 'text' => 'Each page has one primary CTA (not competing actions)'],
                ['key' => 'cta_value_add',      'text' => 'CTAs communicate what the user gets, not just what they do'],
                ['key' => 'cta_urgency',        'text' => 'Where appropriate, CTAs include urgency or incentive'],
            ],
            'Social Proof & Trust Content' => [
                ['key' => 'social_testimonials','text' => 'Customer testimonials are present and specific'],
                ['key' => 'social_case_studies','text' => 'Case studies or success stories are available'],
                ['key' => 'social_logos',       'text' => 'Client or partner logos are displayed'],
                ['key' => 'social_numbers',     'text' => 'Quantified results are cited (e.g. "increased revenue by 40%")'],
                ['key' => 'social_recency',     'text' => 'Social proof content is recent (within 2 years)'],
                ['key' => 'social_attribution', 'text' => 'Testimonials include name, role, and company'],
                ['key' => 'social_video',       'text' => 'Video testimonials or demos are present where relevant'],
            ],
            'Content Depth & Funnel Coverage' => [
                ['key' => 'funnel_awareness',       'text' => 'There is content targeting top-of-funnel (awareness) visitors'],
                ['key' => 'funnel_consideration',   'text' => 'There is content for mid-funnel (comparison/evaluation) visitors'],
                ['key' => 'funnel_decision',        'text' => 'There is content that helps bottom-of-funnel visitors decide'],
                ['key' => 'funnel_faq',             'text' => 'FAQs address common objections'],
                ['key' => 'funnel_blog',            'text' => 'There is a blog or resource section with regular content'],
                ['key' => 'funnel_content_quality', 'text' => 'Content is well-written, scannable, and free of errors'],
                ['key' => 'funnel_internal_links',  'text' => 'Internal linking guides users through the funnel'],
            ],
            'SEO & Discoverability' => [
                ['key' => 'seo_titles',       'text' => 'Page titles are descriptive and keyword-relevant'],
                ['key' => 'seo_meta',         'text' => 'Meta descriptions are present and compelling'],
                ['key' => 'seo_headings',     'text' => 'H1/H2 heading structure is logical and keyword-informed'],
                ['key' => 'seo_url_structure','text' => 'URLs are clean, descriptive, and human-readable'],
                ['key' => 'seo_image_alt',    'text' => 'Images have keyword-relevant alt text'],
                ['key' => 'seo_schema',       'text' => 'Schema markup is implemented for relevant content types'],
                ['key' => 'seo_canonical',    'text' => 'Canonical tags prevent duplicate content issues'],
            ],
            'Blog & Resources' => [
                ['key' => 'blog_frequency',  'text' => 'Blog/resource content is published regularly (at least monthly)'],
                ['key' => 'blog_relevance',  'text' => "Content topics are relevant to the target audience's questions"],
                ['key' => 'blog_ctas',       'text' => 'Blog posts include relevant CTAs to convert readers'],
                ['key' => 'blog_seo_optimised','text' => 'Blog posts target specific keywords'],
                ['key' => 'blog_shareable',  'text' => 'Content is shareable (social share buttons or linked assets)'],
                ['key' => 'blog_author',     'text' => 'Author credibility is established (bio, credentials)'],
                ['key' => 'blog_depth',      'text' => 'Articles are comprehensive enough to rank and provide real value'],
            ],
        ];
    }

    public static function allSections(): array
    {
        return [
            'ux'      => static::uxItems(),
            'content' => static::contentItems(),
        ];
    }
}
