Post Love for phpBB 3.3
==========

Add a simple heart/like button to posts with AJAX toggle.
Originally developed by Stanislav Atanasov ([anavaro](https://github.com/satanasov/postlove)). Now maintained by [Avathar.be](https://www.avathar.be).

#### Version
2.2.5

[![Tests](https://github.com/avatharbe/postlove/actions/workflows/tests.yml/badge.svg?branch=main)](https://github.com/avatharbe/postlove/actions/workflows/tests.yml)

#### Support
- [Support forum](https://www.avathar.be/forum/viewforum.php?f=110)

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
- ACP settings for mini profile counters, button display mode, self-liking and summary periods
- Import tool for migrating data from the Thanks for Posts extension

#### Languages supported
- Bulgarian, Czech, Dutch, English, French, German, Polish, Portuguese (BR), Spanish, Turkish

### Changelog
See [CHANGELOG.md](https://github.com/avatharbe/postlove/blob/main/contrib/CHANGELOG.md) for the full release history.

### Installation
1. [Download the latest release](https://github.com/avatharbe/postlove/releases) and unzip it.
2. Copy the entire contents from the unzipped folder to `/ext/avathar/postlove/`.
3. Navigate in the ACP to `Customise -> Manage extensions`.
4. Find `Post Love` under "Disabled Extensions" and click `Enable`.

### Configuration
1. Navigate to `ACP -> Extensions -> Post Love -> Post Love`.
2. Configure display options (mini profile counters, button mode, summary periods).
3. To manage who can like posts, go to `ACP -> Permissions -> User/Group permissions` and look for `Can like posts` under Misc.

### Uninstallation
1. Navigate in the ACP to `Customise -> Manage extensions`.
2. Click the `Disable` link for `Post Love`.
3. To permanently uninstall, click `Delete Data`, then delete the `postlove` folder from `/ext/avathar/`.

### License
[GNU General Public License v2](https://opensource.org/licenses/GPL-2.0)

© 2015 - 2019 Stanislav Atanasov (anavaro)
© 2026 - Avathar.be (Andy Vandenberghe)
