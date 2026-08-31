# Changelog

All notable changes to the Post Love extension. The current version is also
recorded in `composer.json` and `ext::POSTLOVE_VERSION`.

Entries are grouped per release by type of change: **Security**, **Added**,
**Changed**, **Fixed**, **Removed**, **Documentation**, **Tests & CI**.

## 2.2.6

### Security

- `lovelist::base()` and both `summary_listener` handlers built their forum set from `acl_getf('f_read')` alone. `f_read` is independent of `forum_password` — phpBB gates password protected forums separately via `login_forum_box()` / `FORUMS_ACCESS_TABLE` — so a forum with `f_read` granted but its password not entered by this session was still included. Added `service\forum_access::drop_locked()` (`LEFT JOIN FORUMS_ACCESS_TABLE ON session_id`, mirroring `search.php`'s `$not_in_fid` construction) and call it after every `acl_getf('f_read')` pass in `lovelist.php` and `summary_listener.php`, including the sub-forum branch of `forum_page_summary()`. `avathar_postlove_list` has no permission of its own and is reachable unauthenticated, so this was exploitable via a bare GET to `/postlove/{user_id}` with no session at all

### Fixed

- `acp_postlove_module::main()` never assigned `{U_ACTION}`, so the form posted back to the current URL instead of the module's own action. On the settings-save branch this was a no-op, but `confirm_box()` leaves `confirm_key` on the URL after a confirmed Clean or Import, and `confirm_box()` returns `false` for a second confirmation while a key is present — after already calling `adm_page_header()`. Net effect: a second Clean/Import submit re-rendered the settings page under the confirm title, with no prompt and no error, and skipped the operation
- `importThanks()`'s `INSERT ... FROM {thanks_table}` ran unconditionally once confirmed; `sql_table_exists()` was only checked for the Import-button visibility flag, not before the query itself, so confirming Import after the Thanks for Posts table had been dropped hit a raw SQL error instead of a no-op
- `memberlist_view_user_statistics_after.html` rendered the "Show list with all like actions" row unconditionally, but `main_listener::user_profile_likes()` only assigns `{POSTLOVE_STATS}` when the viewer hasn't set `pf_postlove_hide`. Opted-out users saw the row anyway, as `<a href="">`, with `jquery.modal.js` also unloaded for them (gated on the same variable in `overall_footer_after.html`), so the link just reloaded the profile. Row now gated on `<!-- IF POSTLOVE_STATS -->`, matching the post-notices guard from 2.2.5

### Changed

- `cleanPostLoves()` gained a sibling, `importThanks()`, so `main()` is left doing request handling and confirmation only, with both ACP operations available as named, independently callable methods
- `$db_tools` and the `thanks` table name are now resolved once per request instead of being fetched again for the "items available to import" count

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
