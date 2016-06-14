# WordpressToMarkdown
Converts an entire Wordpress site into markdown (.md) files

## Usage
When instantiating this object you pass in the URL to parse. If this URL is a post or a page it will convert just that page. If it's a page containing just a list of links (i.e. an archive page) then it will parse each post or page listed. Files will be output to the same folder (unless specified), and will be named based on the post date or page name.

*Output page to stdout*
```php
WordpressToMarkDown::print('https://jedi58.wordpress.com/trips/20134-antarctica/');
```

*Output page to file*
```php
WordpressToMarkDown::save('https://jedi58.wordpress.com/trips/20134-antarctica/', __DIR__ . 'output/');
```
