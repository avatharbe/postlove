# Changelog

All relevant changes to the Post Love extension.

## 2.2.6

### Security

- Added `forum_access::drop_locked()` and applied it everywhere a forum set is built from `acl_getf('f_read')`, in `lovelist.php` and `summary_listener.php`; `f_read` ignores forum passwords, so password protected content was leaking, including to unauthenticated requests via the love list route

### Fixed

- Assigned `{U_ACTION}` in the ACP template; it was never set, so a second confirmed Clean or Import silently failed via `confirm_box()`'s `confirm_key` handling instead of running
- Added an `sql_table_exists()` guard before `importThanks()`'s `INSERT`, confirming Import after the Thanks for Posts table was dropped hit a raw SQL error instead of doing nothing
- Gated the love-list link in `memberlist_view_user_statistics_after.html` on `<!-- IF POSTLOVE_STATS -->`, matching the post-notices guard from 2.2.5; it was rendering even for users who opted out via `pf_postlove_hide`, as a dead `<a href="">`
- Gated `inject_topic_like_count()` on `u_postlove_summary`, `pf_postlove_hide`, and bot status; the viewforum heart + count had none of the checks the rest of the feature respects
- Made `topposts_of_period()`'s aggregate page through its results instead of fetching a single page; being board-wide with no forum filter, a single page could fill entirely with posts a viewer can't read, so the panel silently showed fewer posts than configured, or none
- Deleted the like notification only once no likes remain, dropping the 2.2.4 parent-id scoping on the delete; phpBB deduped notifications to one per post, so scoping the delete could clear it early or miss it, depending on who unliked last
- Split the disabled-heart message into `LOGIN_TO_LIKE_POST` and a new `NO_PERMISSION_TO_LIKE_POST`; a registered user whose group simply lacks `u_postlove` was told to log in, which they'd already done
- Capped the love-list modal's width at `500px` (`max-width: 90%` on narrow screens) and switched its fixed `height: 80%` to `max-height: 80%`, so a short like list sizes to its content instead of always filling most of the viewport
- Removed `.modal`'s `background-color: rgba(0, 0, 0, 0.5)` and its `background: #fff`; the former overrode the latter, so the popup first opened as a dark translucent panel, and the hardcoded white card underneath clashed just as badly once confirmed live against a dark style. Row items already carry their own background via the active style's `.row.bg1`/`.bg2`, so the wrapper needs none; the dark overlay behind the modal comes from `.blocker`, styled separately by `jquery.modal.js` itself
- Un-nested `postlove_base.html`'s duplicate `<!-- IF .pagination -->`; the inner copy of the same condition made its `<!-- ELSE -->` branch, which referenced an unassigned `{PAGE_NUMBER}`, unreachable
- Renamed `POSTLOVE_CONTROL` and `ACP_POSTLOVE` to "Post Love" in every language; the settings page heading and, in most languages, the ACP menu entry read differently from the category and the extension's own name
- Pluralized `POSTLOVE_IMPORT_THANKS` (`$language->lang('POSTLOVE_IMPORT_THANKS', $thanks_to_convert)`, proper plural forms per language); it was a single fixed string concatenated with the raw count, so one row read "1 Thanks records able to be imported"
- Replaced `title="{L_LIKED_BY}"` with a new `{L_TOTAL_LIKES_IN_TOPIC}` on the topic-list heart badge; `LIKED_BY` is "post liked by: ", meant to be followed by names the topic list never fetches, so the tooltip rendered as a label with a colon and nothing after it

### Changed

- Extracted `importThanks()` as a sibling to `cleanPostLoves()`; keeps `main()` to request handling and confirmation only, both ACP operations independently callable
- Resolved `$db_tools`/`thanks_table` once per request — was fetched again just for the "items available to import" count
- Removed six unused injected dependencies: `$config`/`$user`/`$root_path`/`$php_ext` from `notifyhelper`, `$cache` from `summary_listener`, and `$config` from the notification type; none were ever read
- Switched the three `release_2_0_0*` migrations to extend `\phpbb\db\migration\migration`; they never set `$profilefield_name`, so `profilefield_base_migration` gave them a bogus `pf_`-column check and, for `drop_timestamp`, a purge revert that deleted from core profile field tables
- Cast the sub-forum id to `(int)` in `forum_page_summary()`'s query and array push, matching the rest of the file, and swapped a manual `post_id NOT IN (implode(...))` for `sql_in_set('post_id', $post_list, true)` in `topposts_of_period()`
- Dropped the dead `?short=1` from `POSTLOVE_STATS`; nothing reads it, `lovelist::base()` already decides the embedded form from `is_ajax()`
- Renamed the ACP form field prefix from `poslove` to `postlove` in `acp_postlove.html`, `acp_postlove_module.php`, and the functional tests; it worked since it was consistent, but read as a typo
- Changed the ten "how many to show" ACP fields from `type="text"` to `type="number"`
- Cleanup: fixed an indentation slip in `main_listener.php`, a double space in two `summary_listener.php` method names, and an unreachable `break` after the returns in `ajaxify.php`'s switch
- Moved the love list row's `<a>` markup out of `lovelist.php` into `postlove_base.html`; the controller now passes `U_POST`/`POST_SUBJECT`/`U_TOPIC`/`TOPIC_TITLE` as plain block vars instead of pre-built HTML, and `LIKE_LINE` is replaced by three connector lang keys (`LOVELIST_LIKED`/`LOVELIST_POST_OF`/`LOVELIST_IN_TOPIC`) so a style can restyle the row without touching PHP

### Removed

- Four `heart-*.png` images under `images/`; nothing references them, the heart icon comes from a FontAwesome glyph in `default.css`

### Documentation

- README's license link now points to `https://` instead of `http://`

## 2.2.5

### Security

- The like/unlike toggle now carries and verifies a CSRF link hash (#39) — it was a state-changing GET with no protection, so an `<img src>` on any page silently liked or unliked a post for every logged-in visitor holding `u_postlove`
- The love list now applies post visibility (#41) — it filtered on `f_read` only, so likes on soft-deleted and unapproved posts were listed, with subject, topic title, both usernames and a working link, to anyone including guests

### Fixed

- Likes surviving a user deletion with a `liked_user_id` pointing at the removed account (#54) — when posts are retained, phpBB re-attributes them to the Anonymous user and the like rows now follow, so counts on those posts and the "likes given" totals of the users who liked them stay intact
- Oracle: dropped `AS` before table and derived-table aliases, which Oracle accepts only before a column alias (#42) — this broke the board index, every forum page and the ACP module. The aliased `UPDATE` in `release_2_0_0`, which also broke purge on MSSQL and SQLite, now runs unaliased
- MSSQL: the most-liked summary showed arbitrary posts instead of the most liked ones (#43) — phpBB's MSSQL driver injects `TOP` into every `SELECT` of a statement passed to `sql_query_limit()`, truncating the inner aggregate before the outer `ORDER BY` applied. The query is now split into two single-`SELECT` statements, ordered and trimmed in PHP after the visibility filter
- Both controllers now always return a `Response` (#44) — an unknown action such as `/postlove/bogus/5` throws a 404 instead of returning `0`, and the love list renders its empty state instead of returning `-1` when the viewer can read no forum; both previously produced HTTP 500 with an untranslated framework message
- Love list `{page}` is now constrained to `\d+` in the route and cast in the controller (#45) — a non-numeric page such as `/postlove/58/page/abc` returned HTTP 500 on PHP 8
- Removed `href` and `data-ajax` from the two `<i>` elements inside the heart button (#46) — `href` is invalid on `<i>`, and because phpBB ajaxifies every element carrying `data-ajax` and its handler does not stop propagation, each click fetched the entire viewtopic page before the real toggle ran
- The `pf_postlove_hide` opt-out left an empty floated div and an empty `href=""` link on every post (#47) — the opt-out skipped the template variables the post notices block is guarded on, so its inverted condition rendered the block with everything blank
- Unliking a post no longer deletes the like notification another user created (#48) — the delete was scoped to the post id only, so it removed every notification for that post regardless of who caused it
- PHP warning on forum pages whose sub-forum has no ACL rows for the viewer (#49) — `acl_getf()` omits those forums entirely, and the summary listener indexed into the missing key
- Love list now runs post subjects and topic titles through the board word censor (#50)
- Duplicate `id` attributes in the most-liked summary loop (#52) — the responsive rules now apply to every row instead of only the first, and `<th align="left">` is replaced with CSS

### Changed

- ACP cleanup tool re-syncs orphaned `liked_user_id` values from the post author, repairing boards where users were deleted before the #54 fix

### Removed

- Two obsolete config entries inherited from the original extension that nothing ever read — `postlove_version` and `postlove_installed_theme` (#51); the canonical version now lives in `ext::POSTLOVE_VERSION`
- The redundant `revert_data()` in `release_1_2_0` (#51), which duplicated the migrator's own reversal of `update_data()` and removed `postlove_summary_query_cache_seconds`, a key no migration adds

### Documentation

- README linked a non-existent testing document and listed ACP settings that no longer exist (#53); the empty version-marker migrations are now documented as intentional
- The changelog moved out of the README into `contrib/CHANGELOG.md`

### Tests & CI

- Enabled the MSSQL job (`RUN_MSSQL_JOBS`), which had been switched off — it reproduces #43 and now passes
- Functional tests no longer assume the database hands out topic 2 / post 3, which made them fail unpredictably on PostgreSQL
- Dropped the unused `force_allow_postlove()` helper, which updated `postlove_use_css` — a config key removed back in 1.1.0

## 2.2.4

### Security

- Added CSRF protection (`add_form_key` / `check_form_key`) and `{S_FORM_TOKEN}` to the ACP settings form
- Whitelisted permitted config keys in the ACP submit handler to prevent arbitrary phpBB config writes via crafted POST keys

### Added

- Missing `NO_ACTIONS_FOUND` language key (empty-state message on the love list page rendered the raw key)
- `revert_data()` so the `postlove_version` config row is removed on uninstall

### Fixed

- Hardcoded English error messages in `is_enableable()` and the love list page title replaced with translatable language keys
- Child-forum `f_read` permission check in the summary listener (was incorrectly checking the parent forum's permission, exposing posts from unreadable sub-forums or hiding posts from readable ones)
- Broken alternating row colours in the most-liked summary (template variable was `ROWCOUNT` instead of `S_ROW_COUNT`)

### Changed

- Cleanup: removed unused notifyhelper dependencies, redundant SQL round-trip in like toggle, dead commented code; added explicit method visibility; standardized HTML5 IDs in the ACP form; refactored INSERT to use `sql_build_array()`

## 2.2.3

### Fixed

- Heart button displaying as a blue rectangle in pbTech and other styles that override `.button` styling (#34)
- Heart icon and like count in post buttons now inherit theme-appropriate colours

## 2.2.2

### Added

- `avathar.postlove.topic_likes` service for cross-extension integration (#33) — other extensions can consume like counts via optional DI without querying the `posts_likes` table directly

## 2.2.1

### Added

- `is_enableable()` check to enforce PHP 8.1+ and phpBB 3.3+ requirements before enabling (#29)

## 2.2.0

### Security

- Blocked like permission for guests — anonymous users can no longer like posts (defence-in-depth)

### Added

- `u_postlove_summary` permission to gate visibility of the most liked posts summary per user group
- Configurable summary position on the index page (above or below the forum list)

### Changed

- UX aligned with Meta Threads conventions (heart icon before count, removed "x" separators)
- Improved ACP option labels and descriptions across all 10 languages
- ACP settings grouped into "Like behaviour" and "Most liked posts summary" fieldsets

### Fixed

- Migration to fix notification type service name after the namespace rename

## 2.1.0

### Security

- Love list URL path traversal (uses `append_sid()` now)

### Added

- Heart count on the topic list (viewforum)
- `u_postlove` permission (per user/group, guests excluded by default)

### Changed

- Namespace changed from `anavaro/postlove` to `avathar/postlove`
- Updated requirements to PHP 8.1+ and phpBB 3.3+
- ACP module refactored to use the DI container instead of globals
- All language files fully translated (were partially English)
- Standardized all file headers with proper copyright attribution

### Fixed

- N+1 query problem in viewtopic (75+ queries reduced to 3)
- PHP 8.2+ compatibility: declared all class properties
- Notification deduplication (swapped `item_id`/`parent_id`)
- Heart icon states (outline = not liked, filled = liked)
- AJAX tooltip updates after like/unlike
- `var_dump()` in ACP, `define()` at file scope, missing `return` in notification
- German notification placeholder bug
- Cyrillic character in HTML closing tags

### Tests & CI

- Migrated CI from Travis to GitHub Actions (PHP 8.1-8.4, MySQL, MariaDB, PostgreSQL, SQLite)
- Updated tests for PHPUnit 9 compatibility


## Provenance

Releases before 2.1.0 were made by the original author under `anavaro/postlove`
and are listed in the [upstream repository](https://github.com/satanasov/postlove/tags).

This project forked from [satanasov/postlove](https://github.com/satanasov/postlove)
in March 2026, at the untagged `2.0.0-b3` beta that had been sitting on `master`
since February 2021. The last upstream tag was `1.1.2`; there was never a released
2.0.0. Some of what is fixed was inherited with that beta rather than introduced
here.
