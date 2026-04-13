<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    /**
     * Génère le Sitemap XML.
     * GET /api/v1/sitemap
     */
    public function sitemap(): Response
    {
        // Cache le sitemap pendant 1 heure (3600 secondes)
        $articles = Cache::remember('sitemap', 3600, function () {
            return Article::published()
                ->select('slug', 'updated_at', 'published_at')
                ->latest('published_at')
                ->get();
        });

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        // Homepage
        $xml .= '<url><loc>' . config('app.frontend_url') . '</loc><changefreq>weekly</changefreq><priority>1.0</priority></url>';

        // Page blog
        $xml .= '<url><loc>' . config('app.frontend_url') . '/blog</loc><changefreq>daily</changefreq><priority>0.9</priority></url>';

        foreach ($articles as $article) {
            $xml .= '<url>';
            $xml .= '<loc>' . config('app.frontend_url') . '/blog/' . $article->slug . '</loc>';
            $xml .= '<lastmod>' . $article->updated_at->toAtomString() . '</lastmod>';
            $xml .= '<changefreq>monthly</changefreq>';
            $xml .= '<priority>0.8</priority>';
            $xml .= '</url>';
        }

        $xml .= '</urlset>';

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    /**
     * Génère le flux RSS.
     * GET /api/v1/feed
     */
    public function rss(): Response
    {
        $articles = Cache::remember('rss_feed', 3600, function () {
            return Article::published()
                ->with('category')
                ->latest('published_at')
                ->limit(20)
                ->get();
        });

        $rss  = '<?xml version="1.0" encoding="UTF-8"?>';
        $rss .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">';
        $rss .= '<channel>';
        $rss .= '<title>sahelstack.tech — Blog</title>';
        $rss .= '<link>' . config('app.frontend_url') . '/blog</link>';
        $rss .= '<description>Articles sur Laravel, Next.js et DevOps par Mahamane Korobara.</description>';
        $rss .= '<language>fr</language>';
        $rss .= '<atom:link href="' . config('app.url') . '/api/v1/feed" rel="self" type="application/rss+xml"/>';

        foreach ($articles as $article) {
            $rss .= '<item>';
            $rss .= '<title><![CDATA[' . $article->title . ']]></title>';
            $rss .= '<link>' . $article->canonical_url . '</link>';
            $rss .= '<guid>' . $article->canonical_url . '</guid>';
            $rss .= '<description><![CDATA[' . $article->excerpt . ']]></description>';
            $rss .= '<pubDate>' . $article->published_at->toRssString() . '</pubDate>';
            if ($article->category) {
                $rss .= '<category>' . $article->category->name . '</category>';
            }
            $rss .= '</item>';
        }

        $rss .= '</channel></rss>';

        return response($rss, 200)->header('Content-Type', 'application/rss+xml');
    }
}
