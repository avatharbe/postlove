Post Love for phpBB 3.3
==========

Add a simple heart/like button to posts with AJAX toggle.
Originally developed by Stanislav Atanasov ([anavaro](https://github.com/satanasov/postlove)). Now maintained by [Avathar.be](https://www.avathar.be).

#### Version
2.2.2

[![Tests](https://github.com/avatharbe/postlove/actions/workflows/tests.yml/badge.svg?branch=main)](https://github.com/avatharbe/postlove/actions/workflows/tests.yml)

#### Support
- [Support forum](https://www.avathar.be/forum)

#### Requirements
- phpBB 3.3.0 or higher
- PHP 8.1 or higher

#### Features 
- Heart button under every post with AJAX toggle (no page reload)
- Outline heart for "not liked", filled heart for "liked"
- Tooltip showing who liked the post, updated live via AJAX
- Like count per topic on the forum topic list (viewforum)
- Like counts (given/received) in user mini profile (configurable)
- Summary of most liked posts by day/week/month/year/ever on index and forum views (configurable)
- Notification when a post is liked (respects UCP notification preferences)
- Permission system (`u_postlove`) to control who can like posts per user/group
- Permission system (`u_postlove_summary`) to control who can see the most liked posts summary
- Configurable summary position (above or below the forum list on the index page)
- ACP settings for CSS, mini profile counters, summary display and cache time
- Import tool for migrating data from the Thanks for Posts extension

#### Languages supported
- Bulgarian, Czech, Dutch, English, French, German, Polish, Portuguese (BR), Spanish, Turkish

### Changelog
- 2.2.2
  - Added `avathar.postlove.topic_likes` service for cross-extension integration (#33)
  - Other extensions can consume like counts via optional DI without querying the posts_likes table directly
- 2.2.1
  - Added `is_enableable()` check to enforce PHP 8.1+ and phpBB 3.3+ requirements before enabling (#29)
- 2.2.0
  - UX aligned with Meta Threads conventions (heart icon before count, removed "x" separators)
  - Improved ACP option labels and descriptions across all 10 languages
  - ACP settings grouped into "Like behaviour" and "Most liked posts summary" fieldsets
  - Added `u_postlove_summary` permission to gate visibility of the most liked posts summary per user group
  - Added configurable summary position on the index page (above or below the forum list)
  - Blocked like permission for guests — anonymous users can no longer like posts (defence-in-depth)
  - Migration to fix notification type service name after namespace rename
- 2.1.0
  - Namespace changed from `anavaro/postlove` to `avathar/postlove`
  - Migrated CI from Travis to GitHub Actions (PHP 8.1-8.4, MySQL, MariaDB, PostgreSQL, SQLite)
  - Updated requirements to PHP 8.1+ and phpBB 3.3+
  - PHP 8.2+ compatibility: declared all class properties
  - Fixed N+1 query problem in viewtopic (75+ queries reduced to 3)
  - Added heart count to topic list (viewforum)
  - Added `u_postlove` permission (per user/group, guests excluded by default)
  - Fixed notification deduplication (swapped item_id/parent_id)
  - Fixed heart icon states (outline=not liked, filled=liked)
  - Fixed AJAX tooltip updates after like/unlike
  - Fixed lovelist URL path traversal (uses `append_sid()` now)
  - Fixed `var_dump()` in ACP, `define()` at file scope, missing `return` in notification
  - ACP module refactored to use DI container instead of globals
  - All language files fully translated (were partially English)
  - Fixed German notification placeholder bug
  - Fixed Cyrillic character in HTML closing tags
  - Standardized all file headers with proper copyright attribution
  - Updated tests for PHPUnit 9 compatibility

### Installation
1. [Download the latest release](https://github.com/avatharbe/postlove/releases) and unzip it.
2. Copy the entire contents from the unzipped folder to `/ext/avathar/postlove/`.
3. Navigate in the ACP to `Customise -> Manage extensions`.
4. Find `Post Love` under "Disabled Extensions" and click `Enable`.

### Configuration
1. Navigate to `ACP -> Extensions -> Post Love -> Post Love`.
2. Configure display options (mini profile counters, button mode, summary periods).
3. To manage who can like posts, go to `ACP -> Permissions -> User/Group permissions` and look for `Can like posts` under Misc.

### Testing
See [contrib/TESTING.md](tests/TESTING.md) for details on running the test suite.

### Uninstallation
1. Navigate in the ACP to `Customise -> Manage extensions`.
2. Click the `Disable` link for `Post Love`.
3. To permanently uninstall, click `Delete Data`, then delete the `postlove` folder from `/ext/avathar/`.

### License
[GNU General Public License v2](http://opensource.org/licenses/GPL-2.0)

© 2015 - 2019 Stanislav Atanasov (anavaro)
© 2026 - Avathar.be (Andy Vandenberghe)
