# WP-Advanced-Recent-Posts
WordPress plugin that shows the recent posts in the widget and in other parts of the your posts or theme code.
License: GPLv2 or later

Plugin on [wordpress.org](https://wordpress.org/plugins/advanced-recent-posts/)

**Introducing new feature in 0.6.13 version: the embedded video instead of the Post Featured Image**

You can use the embedded video (first movie) instead of the Post Featured Image in Responsive Grid Layout. If you have any ideas about this feature or it work not properly, please write me in special topic on <a href="https://wordpress.org/support/topic/new-feature-in-0613-the-embedded-video-instead-of-the-post-featured-image" target="_blank">Support Forum</a>'

Advanced Recent Posts plugin shows the recent posts with thumbnails in two areas:
* widget in your sidebar
* shortcode in any place of your post or theme.

Customization of the plugin is wery simple an flexible:
* Widgets are configured into Dashboard -> Appearance -> Widgets
* Shortcodes are configured in plugin shortcode builder, you will see it in the admin menu of your WordPress

There are two predefined color schemes (for Basic and Grid layout): dark and light, but you can set up your own scheme. Or use natural images.

Better to see once than read a hundred times - see the [live demo](http://demo.lp-tricks.com/) on my website :)

# Changelog

## 0.6.14
* Added support of post offset.
* Added ability to exclude the current post if the shortcode inserted in the post content or near.
* Added ability to display Read more link after excerpt in Responsive Grid Layout.
* Added ability to open links in the same or new window.
* Added support of post subtitles (both for shortcodes and widgets).
* Added the ability (both in shortcodes and widgets) to display only the posts of those categories that belong to the post on the page.
* Deep code refactoring

## 0.6.13
* Added embedded video support. Now the embedded video can be displayed instead of Featured Image in Responsive Grid Layout
* Fixed bug with styles and scripts in Widgets Management
* Fixed issue with disappearing the link to another plugin in WordPress Menu

## 0.6.12
* Added the Post Excerpt settings: now you can show or hide an Excerpt of all Posts in the Responsive Grid, also you can set the Post Excerpt lenght and use or ignore &lt;!-- more --&gt; tag.
* Added new settings of Post height in the Responsive Grid:
  * Height of a Featured Post
  * The minimal height of all Posts
* Fixed issue with incorect height of elements of the Responsive Grid in Chrome and Safari
* Fixed issue with incorrect work of two or more shortcodes on the page

## 0.6.11
* Added text color setting for Basic Layout
* Added text and background color settings for Responsive Grid Layout

## 0.6.10
* Added sorting of the posts by the following parameters:
  * Title
  * Name (post slug)
  * Date created
  * Date modified
  * Random
  * Number of comments
* Fixed issue with incorrect arrange of elements in the Basic Layout

## 0.6.9
* Added the ability to filter the posts by tags (only in a shortcode, in widgets this ability will be available in the next version). You can include or exclude post by a specific tag or multiple tags. Now only work with posts.
* Fixed issue with fixed height of a element in mobile version of the Responsive Grid layout.

## 0.6.9 developer release
* Fixed issue with compatibility with Woocommerce (when columns has the same styles names)
* Refactored the formation of the element style for all layouts

## 0.6.8
* Fixed issue with incorrect columns size in Responsive Grid

## 0.6.7
* Responsive Grid now fully responsive! You can set the width and the number of columns and the page will display all the column if the width of the container allows. Or you can set the number of columns and and posts will be placed over the entire width of the container automatically, their width will change depending on the width of the container.
* In both variants of Responsive Grid on smartphones all posts displays in one column the entire width of the screen.
* Added support for multiple columns in all layouts. Now in the layout can be from 1 to 12 columns, all as in the Bootstrap :)

## 0.6.6
* Fixed issue with incorrect interaction with other posts, comments, plug-ins, etc., which displays the content on the same page, which is inserted a shortcode.

## 0.6.5
* Changes in the widgets and shortcodes - now you can exclude the Posts without Featured Image from the Posts list. 
* Changes in the Fluid Images Layout - now if the Post have no Featured Image, the block with background displayed instead of the Featured Image. Background and text color you can choose in the Shortcode Builder. In widget this feature will be available soon.
* Changes in the Thumbnail Layout - now if the Post have no Featured Image, the block with Thumbnail displayed as a la Drop Cap Layout with the first letter of the Post title. Background and text color you can choose in the Shortcode Builder. In widget this feature will be available soon.
* Fixed some bugs in the Shortcode Builder

## 0.6.4
* Added filter by Post authors in the shortcode and widgets
* Added new color scheme (both widgets and shortcode) - natural image colors, without any overlay

## 0.6.3
* Added Custom Taxonomies Support
* Added Color Picker for the background and text color for the Drop Cap Layout
* Fixed bug with disabled columns in the Shortcode Builder

## 0.6.2
* Added Custom Post Types support

## 0.6.1
* Improved mobile version of the Responsive Grid Layout

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