<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
    <channel>
        <title>MisterioCode &#8212; Blog</title>
        <link>{{ route('blog.index') }}</link>
        <description>Ideas, gu&#237;as y recursos para jugar misterios en equipo.</description>
        <language>es</language>
        <lastBuildDate>{{ optional($posts->first()?->updated_at)->toRfc822String() }}</lastBuildDate>
@foreach ($posts as $post)
        <item>
            <title>{{ $post->title }}</title>
            <link>{{ route('blog.show', $post->slug) }}</link>
            <guid isPermaLink="true">{{ route('blog.show', $post->slug) }}</guid>
            <description><![CDATA[{{ $post->excerpt }}]]></description>
            <pubDate>{{ $post->published_at->toRfc822String() }}</pubDate>
            <author>{{ $post->author?->email ?? config('legal.email') }} ({{ $post->author?->name ?? 'MisterioCode' }})</author>
        </item>
@endforeach
    </channel>
</rss>
