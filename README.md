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
WordpressToMarkDown::save('https://jedi58.wordpress.com/trips/20134-antarctica/', array('path' => __DIR__ . 'output/'));
```

## Creating a Wordpress Archive Page

* Create a new page
* Add `[display-posts posts_per_page="100" order="DESC"]` as the content
 
This will add a list of your posts to the page so that when you save and view the page they will all be listed as links. If you have more than 100 posts, the limit for the code is 100 so you will need to include an offset. If for example you have over 200 posts you could use:

```
[display-posts posts_per_page="100" order="DESC"]
[display-posts posts_per_page="100" offset="100" order="DESC"]
[display-posts posts_per_page="100" offset="200" order="DESC"]
```

This will then add three lists to the page, all of which will be processed.
