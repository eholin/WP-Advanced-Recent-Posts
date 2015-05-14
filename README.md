# WP-Advanced-Recent-Posts
WordPress plugin that shows the recent posts in the widget and in other parts of the your posts or theme code.
License: GPLv2 or later

Plugin on [wordpress.org](https://wordpress.org/plugins/advanced-recent-posts/)

**Introducing new feature in 0.6 version: Responsive Grid Layout**

Responsive Grid Layout based on Masonry script and you can insert the new layout in your page or template with shortcode.
Now Advanced Recent Posts plugin have only one grid layout - based on Medium (300px width) thumbnail size.
Also you can Feature some posts - they will be show in the grid increased.
See [live demo](http://demo.lp-tricks.com/recent-posts/responsive-grid-dark/) of the new layout!

Advanced Recent Posts plugin shows the recent posts with thumbnails in two areas:

* widget in your sidebar
* shortcode in any place of your post or theme.

Customization of the plugin is wery simple an flexible:

* Widgets are configured into Dashboard -> Appearance -> Widgets
* Shortcodes are configured in plugin shortcode builder, you will see it in the admin menu of your WordPress

There are two predefined color schemes (for Basic and Grid layout): dark and light, but you can set up your own scheme.

Better to see once than read a hundred times - see the [live demo](http://demo.lp-tricks.com/) on my website :)

# Changelog

## 0.6
* New layout (shortcode) - Responsive Grid

## 0.5
* Now you can select one or more categories of displayed posts
* Now you can rearrange the date and title
* Now you can display posts in reverse order
* Fixed some bugs

## 0.4
* Added different date and time formats - now date and time format is independent from WP date and time settings
* The custom months localization was removed

## 0.3
* Added shortcode builder in WordPress Backend
* Two layouts (basic and overlay) merged into one: layout with adaptive or fixed fixed width and fluid images
* Added one column and two columns support for all layouts

## 0.2
* Added one more widget  - widget with small thumbnails
* Added frontend for shortcode, only 4 styles:
  * Basic layout
  * Fluid images with dark/light overlay
  * Small thumbnails
  * Recent posts without thumbnails, with date as drop cap

## 0.1
* Initial release - only widget that shows the recent posts in the widget
* Only one style of recent posts in widget

## TODO
- [ ] Add more styles to widget:
  - [x] Thumbnail (default 100px x 100px) and header at right
  - [ ] Large fluid image, header and excerpt at bottom
  - [ ] Without thumbnails, date as drop cap  
- [x] Add shordcode support for displaying recent posts in the theme code
- [ ] Add shortcode styles
  - [õ] Masonry style recent posts
  - [ ] Large fluid thumbnails with excerpt
  - [x] With Thumbnail (default 100px x 100px) and header at right
  - [x] Without thumbnails, post date as drop cap
  - [x] One column and two columns support
- [ ] Add animation in the widget