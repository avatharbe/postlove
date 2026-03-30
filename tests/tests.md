# Postlove Test Suite

## Test Framework

Tests run via the [phpbb-extensions/test-framework](https://github.com/phpbb-extensions/test-framework) GitHub Actions workflow. The CI matrix covers PHP 8.1-8.4 against MySQL 5.6-8.0, MariaDB 10.1-10.5, PostgreSQL 9.5-14, SQLite, and Windows/IIS.

## Test Files

### Unit Tests

#### `tests/controller/controller_ajaxyfy_test.php`

Tests the AJAX like/unlike toggle endpoint (`/postlove/toggle/{post_id}`).

| Data Set | Scenario | Expected |
|----------|----------|----------|
| `no_permission` | User without `u_postlove` permission | `{"error":1}` |
| `anonymous` | Anonymous user (user_id=1) | `{"error":1}` |
| `user_cant_like_own` | Author tries to like own post when `postlove_author_like=false` | `{"error":1}` |
| `no_such_post` | Toggle on non-existent post (id=99) | `{"error":1}` |
| `user_can_like` | Author likes own post when `postlove_author_like=true` | `toggle_action: add` |
| `like` | User likes a post they haven't liked before | `toggle_action: add` |
| `unlike` | User unlikes a post they previously liked | `toggle_action: remove` |

**Fixture:** `tests/controller/fixtures/users.xml` — 4 topics, 4 posts (all by poster_id=2), 6 existing likes, 4 users.

#### `tests/controller/controller_lovelist_test.php`

Tests the love list page (`/postlove/{user_id}`) which shows posts a user has liked or received likes on, filtered by forum read permissions.

| Data Set | Scenario | Expected Rows |
|----------|----------|---------------|
| `normal` | All 3 forums readable, viewing user 2's likes | 6 |
| `test_forum` | Forums 1+2 readable, forum 3 restricted | 5 |
| `test2` | Only forum 1 readable | 1 |

Also includes `test_install` which verifies the `posts_likes` table exists after migration.

**Fixture:** Same as ajaxify tests (`tests/controller/fixtures/users.xml`).

#### `tests/event/main_event_test.php`

Tests the main event listener that renders like buttons/counts on posts during `viewtopic`.

- Verifies `getSubscribedEvents()` returns the correct event list
- Tests like count display, button rendering, and tooltip generation across viewtopic events

**Fixture:** `tests/event/fixtures/users.xml`

#### `tests/event/summary_event_test.php`

Tests the summary panel that shows most-liked posts on the board index and viewforum pages.

**Index page tests** (`test_index_modify_page_title`):

| Data Set | Scenario | Expected Posts |
|----------|----------|----------------|
| `show all` | All time, both forums readable | 5 |
| `show all only in Forum 1` | All time, only forum 1 readable | 4 |
| `anonymous user` | Anonymous viewing, 1 per period | 3 |
| `only this year` | Year period only | 3 |
| `only this month` | Month period only | 2 |
| `only this week` | Week period only | 2 |
| `only today` | Today period only | 1 |
| `none at all` | All period counts = 0 | 0 |

**Forum page tests** (`test_viewforum_modify_page_title`): Same matrix as index tests but scoped to a single forum (forum_id=1) with sub-forum handling.

**Viewforum topic row tests**: Verify that like counts are injected into topic rows on the viewforum page.

**Fixture:** `tests/event/fixtures/summary_data.xml` — 3 forums, 5 posts across 3 topics, 6 likes at specific timestamps (2017-era) for deterministic period boundary testing. Test time is pinned to Unix timestamp 1500000000 (2017-07-14).

### Functional Tests

#### `tests/functional/postlove_acp_test.php`

End-to-end test of the ACP settings page. Logs in as admin, navigates to the Postlove ACP module, and verifies settings can be saved.

#### `tests/functional/postlove_post_test.php`

End-to-end tests of the like functionality in a real phpBB installation.

| Test | Scenario |
|------|----------|
| `test_post` | Create a post, toggle a like via AJAX, verify the like count appears |
| `test_guest_see_loves` | Verify guests can see like counts on posts |
| `test_guests_cannot_like` | Verify guest like toggle returns an error |
| `test_show_likes_given` | Verify the "likes given" count appears in user profiles |

**Helper:** `tests/functional/CssParser.php` — parses CSS for button mode assertions.
**Base class:** `tests/functional/postlove_base.php` — shared setup (creates test posts, provides helper methods).

## Fixtures

All XML fixtures follow phpBB's DbUnit format. Key tables:

- `phpbb_users` — test users with `username_clean` for MySQL unique index compatibility
- `phpbb_posts` / `phpbb_topics` / `phpbb_forums` — test content
- `phpbb_posts_likes` — pre-seeded like records with specific timestamps
