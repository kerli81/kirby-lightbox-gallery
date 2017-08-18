<?php

/**
* Lightbox-Gallery Plugin
*
* @package   Kirby CMS
* @author    Dennis Kerzig <hi@wottpal.com>
* @version   0.2.0
*/


// Helpers
include_once __DIR__ . DS . 'helpers.php';


// Dynamic Tagname
$tagname = c::get('lightboxgallery.kirbytext.tagname', 'gallery');


$kirby->set('tag', $tagname, [
  'attr' => [
    'limit',
    'id',
    'class',
    'page'
  ],
  'html' => function($tag) use ($tagname) {

    // Options
    $class = $tag->attr('class', c::get('lightboxgallery.class', ''));
    $id = $tag->attr('id', c::get('lightboxgallery.id', ''));
    $combine = c::get('lightboxgallery.combine', false);
    $limit = $tag->attr('limit', c::get('lightboxgallery.limit', false));
    $stretch = c::get('lightboxgallery.stretch', true);
    $stretch_last = c::get('lightboxgallery.stretch.last', false);

    // Columns-Definition (get it from config or as tag-attribute)
    $cols = c::get('lightboxgallery.cols', ['min' => 3, 'max' => 4]);
    $cols_attr = columnsStringIsValid($tag->attr('cols', false));
    if ($cols_attr) $cols = $cols_attr;

    $mobilecols = c::get('lightboxgallery.mobilecols', ['min' => 2, 'max' => 2]);
    $mobilecols_attr = columnsStringIsValid($tag->attr('mobilecols', false));
    if ($mobilecols_attr) $mobilecols = $mobilecols_attr;

    // Allow other pages as file-source (esp. for the `kirbytag` function)
    $file_source = $tag;
    $custom_page = $tag->attr('page', false);
    if ($custom_page) $custom_page = page($custom_page);
    if ($custom_page) $file_source = $custom_page;

    // Thumb-Options
    $thumb_provider = strtolower(c::get('lightboxgallery.thumb.provider', 'thumb'));
    $thumb_options = c::get('lightboxgallery.thumb.options', [
      "width" => 800,
      "height" => 800,
      "crop" => true
    ]);

    // Ensure necessary ’thumb.options' are given
    $thumb_options_given = c::get('lightboxgallery.thumb.options', false) != false;
    if ($thumb_provider && $thumb_provider != 'thumb' && !$thumb_options_given) {
      throw new Exception("If something else than 'thumb' is set as 'thumb.provider', 'thumb.options' has to be specified, too.");
    }

    // Gather Images
    $images = str::split($tag->attr($tagname), ' ');
    $image_files = [];
    foreach ($images as $image) {
      $image_file = $file_source->file(trim($image));
      if ($image_file) array_push($image_files, $image_file);
    }

    // Determine Count of displayed Thumbnails
    $use_limit = $limit && $limit < count($image_files);
    $preview_count = $use_limit ? $limit : count($image_files);

    if (!empty($image_files)) {
      // Create Gallery-HTML from Template
      $gallery = tpl::load(__DIR__ . DS . 'template.php', [
        'images' => $image_files,
        'id' => $id,
        'class' => $class,
        'combine' => $combine,
        'thumb_provider' => $thumb_provider,
        'thumb_options' => $thumb_options,
        'preview_count' => $preview_count,
        'cols' => $cols,
        'mobilecols' => $mobilecols,
        'stretch' => $stretch,
        'stretch_last' => $stretch_last
      ], true);

      return html($gallery);
    }
  }
]);
