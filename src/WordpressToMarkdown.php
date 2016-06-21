<?php

namespace Jedi58\WordpressToMarkdown;

use GuzzleHttp\Client;

/**
 * Object to convert a Wordpress site or post to markdown
 */
class WordpressToMarkdown
{
    /**
     * Current version of the converter
     */
    const VERSION = '1.0.0';
    /**
     * @param string[] The array of parts that make up the post
     */
    protected static $post = array(
        'title' => '',
        'subTitle' => '',
        'category' => '',
        'postDate' => '',
        'content' => ''
    );
    /**
     * @param string The content of the page to be parsed
     */
    protected static $content = '';
    /**
     * Retrieve the contents of a Wordpress page for parsing using PSR-7
     * compatible {@link \GuzzleHttp\Client}
     * @param string $url The URL of the page to retrieve
     * @throws \Exception
     */
    private static function fetchUrl($url)
    {
        $client = new \GuzzleHttp\Client([
            'connect_timeout' => 10,
            'headers' => [
                'User-Agent' => 'WordpressToMarkdown-Parser/' . self::VERSION
            ]
        ]);
        $result = $client->get($url);
        if ($result->getStatusCode() >= 400) {
            throw new \Exception(printf('Failed to request %s', $url));
        }
        self::$content = (string) $result->getBody();
    }
    /**
     * Processes the contents of the specified URL and parses the contents
     * @param string $url The URL to parse
     * @param string[] $options Optional
     * @param string $callback The name of the method to use for handling parsed post(s)
     * @return void
     */
    private static function processUrl($url, $options = array(), $callback = 'self::printPost')
    {
        self::fetchUrl($url);
        preg_match_all(
            '/<li.*?class="listing-item".*?>[\w\W]*?<a.*?href="(.*?)".*?>[\w\W]*?<\/a><\/li>/',
            self::$content,
            $match
        );
        if (!empty($match[1])) {
            foreach ($match[1] as $url) {
                self::processUrl($url, $options, $callback);
                sleep(1);
            }
            return;
        }
        self::parseTitles();
        self::parseCategory();
        self::parsePostDate();
        self::parseContent();
        call_user_func($callback, self::formatOutput(), $options);
    }
    /**
     * Parse the title of the page from the retrieved content. This will split the title on the
     * first "-" character to include a sub-title
     */
    private static function parseTitles()
    {
        preg_match('/<h1 class=\"entry\-title\">(.*)<\/h1>/', self::$content, $match);
        if (!empty($match[1])) {
            $match = mb_split(
                '[^0-9]([-–])[^0-9]',
                html_entity_decode($match[1]),
                2
            );
            self::$post['title'] = trim($match[0]);
            self::$post['subTitle'] = !empty($match[1]) ? trim($match[1]) : null;
        }
    }
    /**
     * Parses the postDate from the retrieved content.
     */
    private static function parsePostDate()
    {
        preg_match('/<time.*datetime="(.*?)"/', self::$content, $match);
        if (!empty($match[1])) {
            self::$post['postDate'] = trim($match[1]);
        }
    }
    /**
     * Parses the content of the post and converts HTML markup into standard
     * markdown
     * @todo add support for numbered lists, sublists, youtube, and tables
     */
    private static function parseContent()
    {
        preg_match('/<div class="entry-content">([\w\W]*?)<!-- .entry-content -->/', self::$content, $match);
        if (!empty($match[0])) {
            self::$post['content'] = trim(preg_replace(
                array(
                    '/<div.*?wp-caption.*?>[\w\W]*?href="(.*?)"[\w\W]*?src="(.*?)"[\w\W]*?class="wp-caption-text".*?>([\w\W]+?)<\/p><\/div>/',
                    '/<div.*?wp-caption.*?>[\w\W]*?src="(.*?)"[\w\W]*?class="wp-caption-text".*?>([\w\W]+?)<\/p><\/div>/',
                    '/<p.*?class="embed-youtube".*?src=[\'"](.*?)[\'"].*?<\/p>/',
                    '/(<\/p>|<br.*\/>)/',
                    '/<a.*?href="(.*?)".*?>(.*?)<\/a>/',
                    '/<img.*?src="(.*?)".*?\/>/',
                    '/<\/?em>/',
                    '/<\/?strong>/',
                    '/<blockquote>/',
                    '/<li.*?>/',
                    '/<\/pre>/',
                    '/(<p>|<\/blockquote>|<\/?ul>|<\/li>|<div class="wpcnt">[\w\W]*|<div.*?class="geo.*?>[\w\W]+?<\/div>|<div class="entry-content">[\W]*?)/'
                ),
                array(
                    '[![$3]($2)]($1)',
                    '![$2]($1)',
                    '![Video]($1)',
                    PHP_EOL,
                    '[$2]($1)',
                    '![ ]($1)',
                    '_',
                    '**',
                    '> ',
                    '- ',
                    '```',
                    ''
                ),
                html_entity_decode($match[0])
            ));
        }
    }
    /**
     * Parses the category from the retrieved content
     */
    private static function parseCategory()
    {
        preg_match_all('/<a href="[^"]+\/category\/([^"]+)" rel="category tag">/', self::$content, $match);
        if (!empty($match[1])) {
            self::$post['category'] = $match[1];
        }
    }
    /**
     * Formats the post so it's easier to read
     * @return string The markdown formatted post
     */
    private static function formatOutput()
    {
        return '# ' . self::$post['title'] . PHP_EOL .
            (!empty(self::$post['subTitle']) ?
                '## ' . self::$post['subTitle'] . PHP_EOL :
                null
            ) .
            (!empty(self::$post['postDate']) ?
                self::$post['postDate'] . PHP_EOL :
                null
            ) .
            (!empty(self::$post['category']) ?
                implode(PHP_EOL, self::$post['category']) . PHP_EOL :
                null
            ) .
            PHP_EOL .
            self::$post['content'];
    }
    /**
     * Saves the provided post to a file named by the post date
     * @param string[] $post The post to save to file
     */
    private static function savePost($post, $options)
    {
        $filename = rtrim(
            !empty($options['path']) && is_dir($options['path']) ?
                $options['path'] :
                __DIR__,
            '/'
        ) . '/' . self::getYmdFromPostDate() . '.md';
        $fh = fopen($filename, 'w');
        fwrite($fh, $post);
        fclose($fh);
    }
    /**
     * Prints the post to the current `stdout`
     * @param string[] $post The post to output
     */
    private static function printPost($post, $options)
    {
        print $post;
    }
    /**
     * Takes the post date for the current post and returns the date
     * in the format YYYYMMDD
     * @return string The formatted date string
     */
    public static function getYmdFromPostDate()
    {
        return preg_replace('/(-|T[0-9\-\+\:]+)/', '', self::$post['postDate']);
    }
    /**
     * Processes and outputs the specified URL(s) to `stdout`
     */
    public static function output($url, $options = array())
    {
        self::processUrl($url, $options, 'self::printPost');
    }
    /**
     * Processes and saves the specified URL(s) to files
     */
    public static function save($url, $options = array())
    {
        self::processUrl($url, $options, 'self::savePost');
    }
}
