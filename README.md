# Micro Flat-File Blog Engine

A tiny blog engine written in plain PHP. Your posts are just files, there's no database! 

Why build this when there are so many great frameworks already out there? To
learn, honest! It was a good way to get back into PHP and see how the pieces
of a framework actually fit together. This has its own little router,
controllers, views, config, and the flat-file blog bits. All are written by hand
and kept as small as I could make them. This was my last personal tinker project before 
shifting to AI agentic tools...This is another take on [this project](https://github.com/dlenhart/php-micro-blog-framework).

## What it can do

You drop text files in a folder and they become your blog pages. Posts show up newest
first, get split across pages once you have a few, and each one gets its own
friendly web address. There's a simple theme you can restyle, and a maintenance mode
for when you want to take the site down for a bit. No login, no admin screen —
you publish by adding a file onto your hosts filesystem!

## What you need

- PHP 8.1 or newer
- [Composer](https://getcomposer.org/)

## Running the App

Install the code:

```bash
composer install
```

Then start it up:

```bash
php -S localhost:8000 -t public
```

...and open <http://localhost:8000> in your browser.

Running in Docker:

```bash
docker build -t micro-blog .
docker run --rm -p 8080:8080 micro-blog
```

Open at <http://localhost:8080>.

## Writing a post

Posts go in the `posts/` folder. A post is just one file: a little chunk of JSON info
at the top (meta data: the title, who wrote it, and the date), a line that says
`#content#`, and then whatever you want to write.

```
{
    "title": "Restart Apache",
    "author": "Drew Lenhart",
    "date": "2021-01-09"
}
#content#
Sometimes you just need to restart Apache. Here's how...
```

A few things to keep in mind:

- **The file name becomes the web address.** Name a file `restart-apache.post`
  and it shows up as `/post/restart-apache`. Stick to plain names — letters,
  numbers, dashes, and underscores — and you'll be fine.
- **The date decides the order.** Posts are listed newest first based on the
  date, so write it as `2021-01-09`. Forget the date and the post still shows up,
  it just gets storted last.
- **You can use HTML in the body.** Everything after `#content#` goes straight
  onto the page.
- File extensions can be `.post`, `.txt`, or `.json`.

That's it — save the file in `posts/` and boom, it's live. No rebuild, no restart.

## Templates & Pages

The pages are just PHP files in `resources/views/`. The home page is
`index.view.php`, a single post is `post.view.php`, and so on. The shared bits —
the header with the banner image, the navigation links, and the footer — live in
`resources/views/partials/`, so you only have to edit those once and every page
picks up the change.

For colors and spacing, look at `public/assets/css/styles.css`. The main colors
and the accent are set right at the top, so changing the overall feel is usually
a one-line edit. The banner image sits in `public/assets/images/` — swap that
file out to put your own stamp on it.

## Settings

Everything you can tweak is in `config.php`. The ones you'll care about most:

- **`app.app_name`** — your site's name (shows in the header and the browser tab)
- **`app.posts_dir`** — where your posts are kept
- **`app.posts_per_page`** — how many posts to show before it splits into multi-pages
- **`app.post_preview_text_limit`** — how much of each post to preview on the
  home page

## Adding pages & links

Routes (the web addresses and what they point at) are listed in
`routes/routes.php`:

```php
$router->get('', 'HomeController@home');
$router->get('about', 'PagesController@about');
$router->get('post/{post}', 'PostController@post');
```

The `{post}` part just means "anything here gets handed to the controller" —
that's how `/post/restart-apache` knows which post you want.

## Taking the site down for a bit

Drop a file named `maintenance` in the main folder with this inside:

```json
{
    "maintenance": true,
    "template": "maintenance.view.php",
    "status": 503
}
```

While that's `true`, everyone sees the maintenance page instead of the site.
Flip it back to `false` when you're ready to go live again.

## Where things live

```
bootstrap/bootstrap.php     # starts everything up
public/                     # the web root (and your images/styles)
posts/                      # your posts
resources/views/            # the pages
routes/routes.php           # web addresses
config.php                  # settings
src/                        # the engine itself
tests/                      # the tests
```

## Running the tests

```bash
composer test       # run the tests
composer analyse    # check the code for problems
composer cs-fix     # tidy up the code formatting
```

## License

Open source under the [MIT License](https://opensource.org/licenses/MIT).
