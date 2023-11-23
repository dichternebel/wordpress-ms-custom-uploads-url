# WordPress MS Custom Uploads URL

This WordPress plugin changes the upload URL to use "/files" instead of e.g. "/wp-content/uploads/sites/42"
by using a combination of a WP filter that changes the base URL and two rewrite rules.

You can see this as a functional revival for the old *ms-files.php* concept that has been abandoned since WordPress v3.5.

What it does:
- It will overrule any options set in `upload_url_path`. You should remove any entries there not to get confused later.
- It does not change or touch the `upload_path` option. So you can either leave the default or use whatever local path you want.

It's main purpose is to make staging, migration etc. painless.

In combination with Andrew Patterson's plugin [Absolute Relative URLs](https://wordpress.org/plugins/absolute-relative-urls/)
you will be able to migrate your blog sites with ease.


## Not using multisite?
 
Although this plugin should work for standard WP installations its main purpose is to be used in a multisite network environment.
If you are not running a multisite, changing the options `upload_path` and `upload_url_path` directly would have basically the same result.
 
To do that, just open the *kind-of-hidden* options page https://foo.bar/wp-admin/options.php

## Why a plugin?

I tried to change the options `upload_path` and `upload_url_path` in a multisite network to achieve the same result,
but there is happening some weirdness inside the WP core that always adds `sites/{ID}` to whatever path you set.
Not sure if this is by desgin or a bug, but I hate it and it makes development and staging pain in the a...

So I ended up creating this small plugin that does not touch the core functionality for the folder structure itself,
but *fakes* the resulting URL so to say.

## What about performance?

It's slower than direct downloads, but imho still acceptable. Modern browsers will cache the files anyway.

## Why on GitHub and not on WordPress.org?

Although being field tested, it's not foolproof to set up and use.  
Honestly, I fear that people expect magic happening here only to be disappointed straight away.

## Prerequsites

Requires at least: WordPress v5.1.17 (I mean it! -> 5.0.2 does! not! work! and I did not test it below that version)

Following PHP extension has to be enabled in `php.ini` depending on your setup:  
extension=fileinfo.so || extension=php_fileinfo.dll || extension = fileinfo

Web server requirements:
- Nginx: should work OOB
- Apache: `rewrite_module` has to be loaded
- IIS: [URL Rewrite Module](https://www.iis.net/downloads/microsoft/url-rewrite) has to be installed

Other web servers are untested. Feel free to try out on your own.

## Installation

After having the plugin installed and activated jump over to the settings page of the plugin
and find a detailed description on how to create rewrite rules for your environment.

## Don't
The concept behind this whole thing is to *fake* the URL. So please do not start to manipulate the folder structure for the uploads unless you understand how this is working. Just leave everything default at the beginning!

## Testing

Go to your media library, find an image file and it's URL.
You should see that the path changed from e.g.

`https://[your-domain]/wp-content/uploads/sites/[blog-id]/[year]/[month]/my-image.jpg`  
to  
`https://[your-domain]/files/[year]/[month]/my-image.jpg`

If you have the option `Organize my uploads into month- and year-based folders` disabled then it will look like this:  
`https://[your-domain]/files/my-image.jpg`

If you copy the URL and can open the image in a new browser tab then everything works as expected
and you can start adding media links with the new URL path to your blog content.

If not, check your folder structure! Especially when your WordPress site is not in the root folder.


## Recommended setting

My inner Monk hates the fact that the mainsite upload folder is `wp-content/uploads` per default while the other sites start with `wp-content/uploads/sites/2` and so on.  
If you are like me, you might want to change the option `upload_path` for the mainsite to point to `wp-content/uploads/sites/1` to have everything nice and tidy while leaving the other sites default.


## Adding this to an existing multisite

The plugin will not change any content whatsoever after being activated. That said you can either decide to live with already existing old links
since they work like before or you could e.g. change all links in your `wp_posts` table using a replace SQL statement.

**Enjoy!**