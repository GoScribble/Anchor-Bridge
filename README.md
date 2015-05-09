# Anchor-Bridge
Scribble's Wordpress Bridge allows the Scribble library to interface with your Anchor blog.

###Installation
Drop the scribbleapi.php in your anchor/routes folder then open the anchor/run.php file and go down to line 55, change the following...
```php
else {
	require APP . 'routes/site' . EXT;
}
```
to,
```php
else {
	require APP . 'routes/site' . EXT;
	require APP . 'routes/scribbleapi' . EXT;
}
```

And that's it Scribble can now bridge with your Anchor blog
