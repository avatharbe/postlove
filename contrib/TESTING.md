# Testing Post Love

This extension uses the [phpBB Extensions Test Framework](https://github.com/phpbb-extensions/test-framework) for CI, which runs automatically on every push via GitHub Actions.

## Test structure

```
tests/
  controller/
    controller_ajaxyfy_test.php   # AJAX like/unlike toggle endpoint
    controller_lovelist_test.php  # Love list page with forum permissions
    fixtures/users.xml            # DB fixture for controller tests
  event/
    main_event_test.php           # Main listener event subscriptions
    summary_event_test.php        # Most-liked summary panels (index + viewforum)
    fixtures/users.xml            # DB fixture for main listener tests
    fixtures/summary_data.xml     # DB fixture for summary tests (forums, posts, likes with timestamps)
  functional/
    postlove_base.php             # Base class with helper methods
    postlove_acp_test.php         # ACP settings page
    postlove_post_test.php        # End-to-end like/unlike on viewtopic
    CssParser.php                 # CSS parsing utility
```

## Test categories

### Unit tests (`tests/event/`, `tests/controller/`)

These test individual classes in isolation using mocked dependencies and XML fixture data loaded into a test database.

**main_event_test** verifies that the `main_listener` subscribes to the correct phpBB events:
- `core.permissions` — register the `u_postlove` permission
- `core.viewtopic_modify_post_data` — batch-prefetch likes for all posts on the page
- `core.viewtopic_modify_post_row` — inject heart button and like count into each post
- `core.user_setup` — load the postlove language file
- `core.memberlist_view_profile` — show love list link on user profiles
- `core.delete_posts_after` / `core.delete_user_after` — clean up orphan likes

**controller_ajaxyfy_test** tests the `/postlove/toggle/{post_id}` AJAX endpoint:

| Scenario | User | Permission | Post | Expected |
|----------|------|-----------|------|----------|
| No permission | 1 | denied | 1 | `error:1` |
| Can't like own post | 1 (=poster) | granted | 4 | `error:1` |
| Post doesn't exist | 1 | granted | 5 | `error:1` |
| Like own post (allowed) | 1 (=poster) | granted | 4 | `toggle_action:add` |
| Like someone's post | 2 | granted | 3 | `toggle_action:add` |
| Unlike (already liked) | 2 | granted | 1 | `toggle_action:remove` |

**controller_lovelist_test** tests the `/postlove/{user_id}` love list page, verifying that forum read permissions filter visible likes:

| Scenario | Forums readable | Likes visible |
|----------|----------------|---------------|
| All forums | 1, 2, 3 | 6 |
| One forum restricted | 1, 2 | 5 |
| Two forums restricted | 1 only | 1 |

**summary_event_test** tests the most-liked-posts summary panels shown on the board index and viewforum pages. It uses a fixed test timestamp (`1500000000` = July 14, 2017) so time-period calculations (today, this week, this month, this year, ever) are deterministic. The fixture contains likes with timestamps spread across these periods:

| Like timestamp | Period it falls in |
|---------------|-------------------|
| 1499999000 | Today (within 1 day of test time) |
| 1499900000 | This week |
| 1497000000 | This year |
| 1463000000 | Ever (outside current year) |
| 149999901 | Ever (far past) |
| 1499300 | Ever (far past) |

### Functional tests (`tests/functional/`)

These run against a live phpBB installation with a real database and web server. They test the full stack including template rendering, ACP forms, and HTTP requests.

**postlove_acp_test** verifies the ACP settings page loads and displays the expected language keys.

**postlove_post_test** covers the complete user journey:
1. **test_post** — Create a topic and reply, toggle like in both display modes (inline and button), verify counts
2. **test_guest_see_loves** — Verify guests can see like counts
3. **test_guests_cannot_like** — Verify the AJAX toggle is rejected for guests
4. **test_show_likes_given** — Toggle all 4 ACP configurations for likes given/received counters and verify each
5. **test_show_list** — Load the love list page and verify it displays the correct number of entries

## Running tests locally

Tests require a phpBB development environment. The extension must be located at `phpBB/ext/avathar/postlove/` relative to the phpBB root.

```bash
# From the phpBB root directory
# Run all extension tests
phpBB/vendor/bin/phpunit --configuration phpBB/ext/avathar/postlove/phpunit.xml.dist

# Run only unit tests (no functional)
phpBB/vendor/bin/phpunit --configuration phpBB/ext/avathar/postlove/phpunit.xml.dist --testsuite "Extension Test Suite"

# Run only functional tests (requires web server + database)
phpBB/vendor/bin/phpunit --configuration phpBB/ext/avathar/postlove/phpunit.xml.dist --testsuite "Extension Functional Tests"
```

## CI configuration

GitHub Actions runs via the reusable [phpbb-extensions/test-framework](https://github.com/phpbb-extensions/test-framework) workflow. Configuration is in `.github/workflows/tests.yml`:

- **PHP versions:** 8.1, 8.2, 8.3, 8.4
- **Databases:** MySQL 5.6-8.0, MariaDB 10.1-10.5, PostgreSQL 9.5-14, SQLite3
- **Platforms:** Linux, Windows (IIS)
- **Checks:** PHP CodeSniffer, EPV (Extension Pre Validator), ICC image profiles, unit tests, functional tests
- **MSSQL:** Disabled (raw SQL subqueries are not cross-compatible with SQL Server)

## Fixtures

Test fixtures are XML files that seed the test database. They follow phpBB's `phpbb_database_test_case` format.

### `tests/controller/fixtures/users.xml`
- 4 topics (one per forum, forums 1-4)
- 4 posts (all by poster_id=1)
- 6 likes (users 1-3 liked various posts)
- 1 user (Test user, user_id=1)

### `tests/event/fixtures/users.xml`
- 4 topics (one per forum)
- 4 posts (all by poster_id=3)
- 6 likes with `liked_user_id` column (all liked_user_id=3)
- 2 users (Test user id=3, Test user 2 id=2)

### `tests/event/fixtures/summary_data.xml`
- 2 forums (Forum 1 parent, Forum 2 child)
- 3 topics (2 in forum 1, 1 in forum 2)
- 5 posts with explicit `post_time` values (1000-5000)
- 6 likes with timestamps across different time periods relative to test time 1500000000
- 2 users with `user_colour` for username display testing
