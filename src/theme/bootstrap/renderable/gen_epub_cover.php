<? renderable( $p ); ?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <title><?= he( $p['title'] ) ?> - Cover</title>
    <style type="text/css"> img { max-width: 100%; } </style>
  </head>
  <body>
    <div id="cover-image">
      <img src="<?= he( path_to_filename( $p['cover'] ) ) ?>" alt="<?= he( $p['title'] ) ?>"/>
    </div>
  </body>
</html>
