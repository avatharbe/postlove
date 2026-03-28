Post Love for phpBB 3.3
==========

Add a simple heart/like button to posts with AJAX toggle.
Originally developed by Stanislav Atanasov ([anavaro](https://github.com/satanasov/postlove)). Now maintained by [Avathar.be](https://www.avathar.be).

#### Version
2.1.0-a1

[![Tests](https://github.com/avatharbe/postlove/actions/workflows/tests.yml/badge.svg?branch=main)](https://github.com/avatharbe/postlove/actions/workflows/tests.yml)

#### Support
- [Support forum](https://www.avathar.be/forum)

#### Requirements
- phpBB 3.3.0 or higher
- PHP 8.1 or higher

#### Features
- Heart button under every post with AJAX toggle (no page reload)
- Tooltip showing who liked the post
- Like counts (given/received) in user mini profile (configurable)
- Summary of most liked posts by day/week/month/year/ever on index and forum views (configurable)
- Notification when a post is liked
- ACP settings for CSS, mini profile counters, summary display and cache time

#### Languages supported
- Bulgarian, Czech, Dutch, English, French, German, Polish, Portuguese (BR), Spanish, Turkish

### Changelog
- 2.1.0-a1
  - Namespace changed from `anavaro/postlove` to `avathar/postlove`
  - Migrated CI from Travis to GitHub Actions
  - Updated requirements to PHP 8.1+ and phpBB 3.3+

### Installation
1. [Download the latest release](https://github.com/avatharbe/postlove/releases) and unzip it.
2. Copy the entire contents from the unzipped folder to `/ext/avathar/postlove/`.
3. Navigate in the ACP to `Customise -> Manage extensions`.
4. Find `Post Love` under "Disabled Extensions" and click `Enable`.

### Uninstallation
1. Navigate in the ACP to `Customise -> Manage extensions`.
2. Click the `Disable` link for `Post Love`.
3. To permanently uninstall, click `Delete Data`, then delete the `postlove` folder from `/ext/avathar/`.

### License
[GNU General Public License v2](http://opensource.org/licenses/GPL-2.0)

© 2015 - 2019 Stanislav Atanasov (anavaro)
© 2026 - Avathar.be (Andy Vandenberghe)
