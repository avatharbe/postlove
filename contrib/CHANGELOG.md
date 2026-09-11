# Changelog

Notable changes to the Post Love extension.

## 2.2.7

### Added

- Replaced the single profile-field opt-out with four independent preferences under **UCP > Board preferences > Edit global settings** (raised during [#55](https://github.com/avatharbe/postlove/issues/55)). Each preference has its own explanation and lets users hide one of the following:
  - The like button on posts.
  - The “Likes” link and likes given/received counts on their own profile and posts.
  - The like count beside each topic in the topic list.
  - The most-liked-posts summaries on the board index and forum pages.
- Migrated the old `postlove_hide` value to the new like-button preference. Previously, that field controlled all four features without explaining its effect. All four new preferences default to “No” (visible), matching the old field's default.
- Extended `user_postlove_hide_profile` to hide the user's likes given/received counts beside their posts. Previously, only administrators could control these counters through `postlove_show_likes` and `postlove_show_liked`. Each post follows its author's preference, which `prefetch_likes()` now loads alongside the counts.

### Changed

- Disabled self-liking by default: `postlove_author_like` now defaults to `0` instead of `1`.
- Set `postlove_index_most_liked_this_week` and `postlove_index_most_liked_ever` to default to `1`, instead of `2` and `0`. These defaults now match the equivalent forum-page settings, as the other time periods already did.
- On upgrade, these defaults change only where the stored value still matches the old default. Values that differ from the old defaults are preserved.

### Fixed

- Fixed the missing heart in button mode (`postlove_show_button = 1`) when no other post actions were available ([#55](https://github.com/avatharbe/postlove/issues/55)). Prosilver omits its button list when the viewer cannot edit, delete, report, warn, view info, or quote, so the existing hook had nowhere to render the heart. The shared `postlove_button_li.html` partial now renders through either the existing `viewtopic_body_post_buttons_after` hook or a fallback `viewtopic_body_post_buttons_list_after` hook with its own button list. Mutually exclusive conditions prevent duplicate hearts.
- Made the likes given/received counters easier to identify in the post sidebar. Both rows now have visible **Likes given:** and **Likes received:** labels, replacing icons and numbers that could only be distinguished by hovering. The counters now appear below Contact, using `viewtopic_body_contact_fields_after` instead of `viewtopic_body_postrow_custom_fields_after`.

## 2.2.6

### Security

- Prevented password-protected forum content from appearing in the love list and most-liked summaries without access. The `f_read` permission does not check forum passwords. The new `forum_access::drop_locked()` filter now checks every forum set built from `acl_getf('f_read')` in `lovelist.php` and `summary_listener.php`, including guest requests to the love list.

### Fixed

- Fixed repeated ACP Clean or Import operations silently failing after confirmation. The ACP template now receives `{U_ACTION}`, which `confirm_box()` needs to handle the confirmation key correctly.
- Prevented an SQL error when the Thanks for Posts table is removed before an import is confirmed. `importThanks()` now checks `sql_table_exists()` before inserting records.
- Removed the empty “Likes” profile link for users who opted out through `pf_postlove_hide`. The link in `memberlist_view_user_statistics_after.html` now checks `POSTLOVE_STATS`, matching the post-notices guard added in 2.2.5.
- Made topic-list like counts respect `u_postlove_summary`, `pf_postlove_hide`, and bot status. `inject_topic_like_count()` previously skipped these checks.
- Fixed most-liked summaries showing too few posts, or none, when the initial results contained posts the viewer could not read. `topposts_of_period()` now pages through the aggregate results to find enough visible posts.
- Fixed notification removal when several users liked the same post. phpBB combines these into one notification per post, so the notification is now deleted only when no likes remain. This replaces the parent-ID restriction introduced in 2.2.4.
- Added `NO_PERMISSION_TO_LIKE_POST` for signed-in users who lack `u_postlove`. They previously saw `LOGIN_TO_LIKE_POST`, incorrectly asking them to log in again.
- Made the love-list popup fit its content. Its width is capped at `500px`, with `max-width: 90%` for narrow screens, and `max-height: 80%` replaces the fixed `height: 80%`.
- Removed conflicting background colours from the love-list popup. Rows now use the active style's `.row.bg1` and `.bg2` backgrounds, avoiding a dark translucent panel or a hardcoded white card on dark styles. The separate `.blocker` overlay remains managed by `jquery.modal.js`.
- Removed a duplicate pagination condition in `postlove_base.html`. It made the fallback branch unreachable; that branch also referenced an unassigned `{PAGE_NUMBER}`.
- Standardized the ACP menu entry and settings-page heading as “Post Love” in every language through `POSTLOVE_CONTROL` and `ACP_POSTLOVE`.
- Added proper plural forms to `POSTLOVE_IMPORT_THANKS` in every language. The import count is now passed to the language system instead of being appended to a fixed string.
- Replaced the topic-list heart tooltip with `TOTAL_LIKES_IN_TOPIC`. It previously used `LIKED_BY`, a label intended to precede usernames, leaving a trailing colon with no names.

### Changed

- Extracted `importThanks()` into a separate method alongside `cleanPostLoves()`. Both operations can now be called independently, while `main()` handles requests and confirmation.
- Resolved `$db_tools` and `thanks_table` once per request instead of resolving them again to count records available for import.
- Removed six unused injected dependencies: `$config`, `$user`, `$root_path`, and `$php_ext` from `notifyhelper`; `$cache` from `summary_listener`; and `$config` from the notification type.
- Changed the three `release_2_0_0*` migrations to extend `\phpbb\db\migration\migration`. They do not define profile fields, so their previous base class caused an invalid `pf_` column check and, for `drop_timestamp`, a purge reversal that deleted data from core profile-field tables.
- Cast sub-forum IDs to integers in `forum_page_summary()` and replaced the manually assembled `NOT IN` clause in `topposts_of_period()` with `sql_in_set()`.
- Removed the unused `?short=1` parameter from `POSTLOVE_STATS`. `lovelist::base()` already uses `is_ajax()` to choose the embedded layout.
- Corrected the ACP form-field prefix from `poslove` to `postlove` in the template, module, and functional tests.
- Changed all ten ACP summary-count fields from text inputs to number inputs.
- Cleaned up indentation in `main_listener.php`, extra spacing in two `summary_listener.php` method declarations, and an unreachable `break` in `ajaxify.php`.
- Moved love-list link markup from `lovelist.php` into `postlove_base.html`, allowing styles to customize rows without changing PHP. The controller now supplies `U_POST`, `POST_SUBJECT`, `U_TOPIC`, and `TOPIC_TITLE` as plain template variables. The `LIKE_LINE` language key is replaced by `LOVELIST_LIKED`, `LOVELIST_POST_OF`, and `LOVELIST_IN_TOPIC`.

### Removed

- Removed four unused `heart-*.png` images. The heart icon already uses a FontAwesome glyph defined in `default.css`.

### Documentation

- Updated the README's license link to use HTTPS.

## 2.2.5

### Security

- Added CSRF protection to like/unlike requests (#39). Previously, an image embedded on another page could trigger a like or unlike for a signed-in visitor with `u_postlove` permission.
- Made the love list respect post visibility (#41). Previously, it checked only `f_read`, exposing the subjects, topic titles, usernames, and links of soft-deleted or unapproved posts, including to guests.

### Fixed

- Preserved likes correctly when a user is deleted but their posts are retained (#54). Like records now follow phpBB's reassignment of those posts to Anonymous, keeping post counts and other users' “likes given” totals intact.
- Restored Oracle compatibility by removing `AS` from table and derived-table aliases (#42). These aliases broke the board index, forum pages, and ACP module. Also removed the alias from the `release_2_0_0` migration's `UPDATE`, which affected purge on Oracle, MSSQL, and SQLite.
- Fixed MSSQL summaries showing arbitrary posts instead of the most-liked posts (#43). The driver applied `TOP` to the inner query before sorting. The aggregate is now split into two single-`SELECT` queries, with results sorted and limited in PHP after visibility filtering.
- Made both controllers always return a `Response` (#44). Unknown actions now return HTTP 404, and the love list shows its empty state when the viewer cannot read any forum. Both cases previously produced HTTP 500 errors.
- Restricted the love-list `{page}` route parameter to digits and cast it in the controller (#45). Non-numeric page values previously caused HTTP 500 errors on PHP 8.
- Removed invalid `href` and duplicate `data-ajax` attributes from the heart button's two inner `<i>` elements (#46). Each click previously fetched the full topic page before sending the actual like/unlike request.
- Removed empty post-notice markup for users who opted out through `pf_postlove_hide` (#47). An inverted template condition previously left an empty floated container and an empty link on each post.
- Restricted notification deletion so unliking a post would not remove another user's like notification (#48). The deletion previously matched only the post ID.
- Prevented PHP warnings when a sub-forum had no ACL entry for the viewer (#49). The summary listener now handles forums omitted by `acl_getf()`.
- Applied the board's word censor to post subjects and topic titles in the love list (#50).
- Removed duplicate HTML IDs from most-liked summary rows so responsive rules apply to every row (#52). Also replaced `<th align="left">` with CSS.

### Changed

- Updated the ACP cleanup tool to repair orphaned `liked_user_id` values from the current post author. This repairs records left by user deletions before the fix for #54.

### Removed

- Removed the unused `postlove_version` and `postlove_installed_theme` configuration entries inherited from the original extension (#51). The canonical version is now defined in `ext::POSTLOVE_VERSION`.
- Removed the redundant `release_1_2_0::revert_data()` method (#51). It duplicated the migrator's automatic reversal and removed `postlove_summary_query_cache_seconds`, a configuration key no migration creates.

### Documentation

- Removed a broken testing-document link and obsolete ACP settings from the README (#53). Documented that empty version-marker migrations are intentional.
- Moved the changelog from the README to `contrib/CHANGELOG.md`.

### Tests & CI

- Re-enabled the MSSQL CI job through `RUN_MSSQL_JOBS`. It reproduces the issue in #43 and now passes.
- Removed assumptions about fixed topic and post IDs from functional tests, preventing unpredictable failures on PostgreSQL.
- Removed the unused `force_allow_postlove()` test helper. It updated `postlove_use_css`, a configuration key removed in 1.1.0.

## 2.2.4

### Security

- Added CSRF protection to the ACP settings form using `add_form_key()`, `check_form_key()`, and `{S_FORM_TOKEN}`.
- Restricted the ACP submit handler to permitted configuration keys, preventing crafted requests from changing arbitrary phpBB settings.

### Added

- Added the missing `NO_ACTIONS_FOUND` translation key for the love list's empty-state message.
- Added `revert_data()` to remove the `postlove_version` configuration entry on uninstall.

### Fixed

- Replaced hardcoded English compatibility errors and the love-list page title with translatable language keys.
- Fixed summary permissions for child forums. The listener previously checked the parent forum's `f_read` permission, which could expose unreadable child-forum posts or hide readable ones.
- Restored alternating row colours in most-liked summaries by replacing `ROWCOUNT` with `S_ROW_COUNT`.

### Changed

- Removed unused `notifyhelper` dependencies, a redundant database query in the like toggle, and obsolete commented code.
- Added explicit method visibility, standardized HTML5 IDs in the ACP form, and changed the insert query to use `sql_build_array()`.

## 2.2.3

### Fixed

- Fixed the heart button appearing as a blue rectangle in pbTech and other styles that override `.button` styling (#34).
- Made the heart icon and like count inherit colours appropriate to the active style.

## 2.2.2

### Added

- Added the `avathar.postlove.topic_likes` service for integration with other extensions (#33). Extensions can access like counts through optional dependency injection without querying `posts_likes` directly.

## 2.2.1

### Added

- Added an `is_enableable()` check to enforce PHP 8.1+ and phpBB 3.3+ requirements before the extension is enabled (#29).

## 2.2.0

### Security

- Added an explicit check preventing guests from liking posts.

### Added

- Added the `u_postlove_summary` permission to control access to most-liked summaries by user group.
- Added an option to place the index summary above or below the forum list.

### Changed

- Aligned the like display with Meta Threads conventions: the heart now precedes the count, and “x” separators have been removed.
- Improved ACP labels and descriptions in all ten languages.
- Grouped ACP settings into **Like behaviour** and **Most liked posts summary** sections.

### Fixed

- Added a migration to correct the notification type's service name after the namespace rename.

## 2.1.0

### Security

- Fixed path traversal in love-list URLs by using `append_sid()`.

### Added

- Added a heart and like count to the topic list.
- Added the `u_postlove` permission for users and groups, excluding guests by default.

### Changed

- Changed the namespace from `anavaro/postlove` to `avathar/postlove`.
- Raised the minimum requirements to PHP 8.1 and phpBB 3.3.
- Refactored the ACP module to use the dependency-injection container instead of global variables.
- Completed translations in all language files, replacing remaining English text.
- Standardized file headers and copyright attribution.

### Fixed

- Reduced topic-page database queries from more than 75 to 3 by eliminating repeated per-post queries.
- Declared all class properties for PHP 8.2+ compatibility.
- Fixed notification deduplication by correcting the use of `item_id` and `parent_id`.
- Corrected heart states: an outline means the post is not liked; a filled heart means it is liked.
- Updated tooltips after AJAX like/unlike actions.
- Removed a stray `var_dump()` from the ACP, corrected a file-level `define()`, and added a missing return in the notification code.
- Fixed a placeholder in the German notification translation.
- Removed a Cyrillic character from HTML closing tags.

### Tests & CI

- Migrated CI from Travis to GitHub Actions, covering PHP 8.1–8.4 with MySQL, MariaDB, PostgreSQL, and SQLite.
- Updated tests for PHPUnit 9 compatibility.

## Provenance

Releases before 2.1.0 were published by the original author under `anavaro/postlove`. They are listed in the [upstream repository](https://github.com/satanasov/postlove/tags).

This project forked from [satanasov/postlove](https://github.com/satanasov/postlove) in March 2026, based on the untagged `2.0.0-b3` beta that had been on `master` since February 2021. The last upstream tag was `1.1.2`; version 2.0.0 was never released. Some fixes listed here address issues inherited from that beta.
