# Postlove Test Suite

## What are tests and why do we write them?

Automated tests are PHP scripts that call the extension's code and then verify the result is what we expect. Instead of manually clicking through a forum to check that liking a post works, a test does it in milliseconds and tells you immediately if something is broken.

Tests also act as a safety net during refactoring. If you rename a method or change how a query works and a test fails, you know exactly which behaviour broke — before it reaches a live board.

## Types of tests in this suite

**Unit tests** test a single class or method in isolation. Dependencies like the database, the template engine, or the permission system are either replaced with fakes (called *mocks*) or run against a small controlled dataset. Unit tests are fast and pinpoint failures precisely.

**Functional tests** test the entire extension running inside a real phpBB installation. They drive a browser-like HTTP client, log in, click things, and check what appears on the page. They are slower and harder to debug but catch problems that only show up when all the pieces work together.

## Key concepts

### Fixtures

A fixture is an XML file that pre-fills the test database with known data before each test runs. After each test the database is reset, so tests cannot accidentally affect each other. All fixture files live in `tests/*/fixtures/` and follow phpBB's DbUnit format.

### Mocks

Many classes depend on other objects — the database, the template, the auth system. When testing one class in isolation, you replace those dependencies with *mock* objects. A mock is a stand-in that you can configure to return whatever value a test needs, and that records whether it was called. This means a test for the like button renderer does not need a real running database — it just needs the prefetched data the renderer would have already received.

### Data providers

Rather than writing one test method per scenario, PHPUnit supports *data providers* — a method that returns a list of input/expected pairs. The test method is then called once for each pair. You will see `@dataProvider` annotations in the test files. The table rows in this document that share the same test method name correspond to those data-provider cases.

### Reflection

PHP's `ReflectionClass` lets a test read `protected` or `private` properties on a class without exposing them publicly. It is used here to inspect internal caches after a prefetch query runs, confirming the query populated the right data structure.

---

## Test Framework

Tests run via the [phpbb-extensions/test-framework](https://github.com/phpbb-extensions/test-framework) GitHub Actions workflow. The CI matrix covers PHP 8.1–8.4 against MySQL 5.6–8.0, MariaDB 10.1–10.5, PostgreSQL 9.5–14, SQLite, and Windows/IIS.

To run the tests locally, follow the setup instructions in the test-framework repository. The short version is: install phpBB in a test directory, copy the extension into `ext/avathar/postlove/`, then run `phpunit` from the phpBB root.

---

## Test Files

### Unit Tests

---

#### 1. `tests/controller/controller_ajaxyfy_test.php`

**What this code does:** When a logged-in user clicks the heart icon on a post, the browser sends an AJAX request to `/postlove/toggle/{post_id}`. The `ajaxify` controller handles this request. It checks whether the user is allowed to like that post and then either adds or removes the like, returning a small JSON response the browser uses to update the button without reloading the page.

**What the tests check:** Every gate that can block a like request, plus the two success paths (adding and removing a like). Each test case sets up a different user identity or post state and verifies the JSON response matches what the client expects.

| # | Data Set | Scenario | Expected |
|---|----------|----------|----------|
| 1.1 | `no_permission` | User does not have the `u_postlove` permission — the board admin has not granted them the ability to like posts | `{"error":1}` |
| 1.2 | `anonymous` | User is not logged in (`user_id=1` is phpBB's built-in anonymous user) | `{"error":1}` |
| 1.3 | `user_cant_like_own` | The user is the author of the post and the admin has turned off self-liking (`postlove_author_like=false`) | `{"error":1}` |
| 1.4 | `no_such_post` | The post ID in the URL does not exist in the database (id=99) | `{"error":1}` |
| 1.5 | `user_can_like` | Author tries to like their own post, but the admin has allowed self-liking (`postlove_author_like=true`) | `"toggle_action":"add"` |
| 1.6 | `like` | A regular user likes a post they have not liked before | `"toggle_action":"add"` |
| 1.7 | `unlike` | A regular user clicks the heart again on a post they already liked — this removes the like | `"toggle_action":"remove"` |

**Fixture:** `tests/controller/fixtures/users.xml` — contains 4 topics, 4 posts (all written by poster_id=2), 6 pre-existing likes in `phpbb_posts_likes`, and 4 users. Having pre-existing likes in the fixture is what makes the "unlike" test possible without needing to run a "like" first.

---

#### 2. `tests/controller/controller_lovelist_test.php`

**What this code does:** The love list page (`/postlove/{user_id}`) shows all posts that a specific user has liked. Posts in forums the *viewer* cannot read are silently excluded — a user who cannot see a restricted forum should not be able to discover its content via someone else's like list.

**What the tests check:** That the migration created the required database table (smoke test), and that the forum read permission filter correctly reduces the visible result set.

| # | Data Set | Scenario | Expected Rows |
|---|----------|----------|---------------|
| 2.1 | `test_install` | Check that the `posts_likes` table was created by the extension's migration. If this fails it means the migration did not run or ran incorrectly. | Table exists |
| 2.2 | `normal` | The viewer can read all 3 forums. User 2 has 6 liked posts spread across those forums. | 6 rows rendered |
| 2.3 | `test_forum` | Forums 1 and 2 are readable, forum 3 is restricted. One of user 2's likes is on a post in forum 3, so it is hidden. | 5 rows rendered |
| 2.4 | `test2` | Only forum 1 is readable. User 3 only has likes on posts in forums 2 and 3 except for one in forum 1. | 1 row rendered |

**How the row count is verified:** The test sets an expectation on the template mock — it asserts that `assign_block_vars('liked_posts', ...)` is called exactly N times. Each call adds one row to the page, so the call count equals the number of visible liked posts.

**Fixture:** Same as the ajaxify tests — `tests/controller/fixtures/users.xml`.

---

#### 3. `tests/event/main_event_test.php`

**What this code does:** phpBB fires named *events* at key moments — when a topic page is being built, when a post is deleted, and so on. The `main_listener` class registers itself to handle several of these events. The most important handler is `modify_post_row`, which runs once per post on a topic page and injects the like button, the like count, the tooltip text, and the disable flag into the template data for that post.

There is also a performance-critical prefetch step: rather than running 3 database queries per post (which would be very slow on a topic with 50 replies), `prefetch_likes` runs 1–3 queries once for the whole page and stores the results in memory. `modify_post_row` then reads from that in-memory cache — no extra queries.

Finally, `clean_posts_after` and `clean_users_after` keep the `posts_likes` table tidy when phpBB deletes posts or user accounts.

**What the tests check:**

| # | Test | Handler | Scenario |
|---|------|---------|----------|
| 3.1 | `test_getSubscribedEvents` | — | Verifies the listener registers itself for exactly the right set of phpBB core events. If an event name is misspelled the handler is silently never called — this test catches that. |
| 3.2 | `test_clean_posts_after` | `clean_posts_after` | When phpBB permanently deletes posts, all likes on those posts must be removed from `posts_likes`. Tested with three cases: delete one post (3 rows remain), delete two posts (1 row remains), delete a non-existent post (nothing changes — 6 rows remain). |
| 3.3 | `test_clean_users_after` | `clean_users_after` | When phpBB permanently deletes a user account, all likes *given* by that user must be removed. Same three-case pattern: one user deleted, two users deleted, non-existent user. |
| 3.4 | `test_prefetch_likes` | `prefetch_likes` | After prefetch runs for posts 1–3, the three internal caches are inspected via `ReflectionClass`. Post 1 has likers users 3 and 2 (user 4 liked it too but has no `phpbb_users` row, so the JOIN excludes them). User 3 gave 3 likes total; user 3 received all 6 likes in the fixture. |
| 3.5 | `test_modify_post_row_counts` | `modify_post_row` | Post 1 has 2 likers. The viewer (user 5) has permission and has not liked it. Checks that `POST_LIKERS_COUNT=2`, `POST_LIKE_CLASS='like'` (outline heart), `POST_LIKE_URL` is set, and `DISABLE` is not present (the button is active). |
| 3.6 | `test_modify_post_row_current_user_liked` | `modify_post_row` | The viewer is user 2, who liked post 1 in the fixture. Checks that `POST_LIKE_CLASS='liked'` (filled heart) and `ACTION_ON_CLICK='CLICK_TO_UNLIKE'` (tooltip says "click to unlike"). |
| 3.7 | `test_modify_post_row_no_permission` | `modify_post_row` | The viewer exists and is logged in but does not have the `u_postlove` permission. Checks that `DISABLE=1` and `ACTION_ON_CLICK='LOGIN_TO_LIKE_POST'` (button is greyed out with an explanatory tooltip). |
| 3.8 | `test_modify_post_row_self_like_disabled` | `modify_post_row` | `postlove_author_like=0` is set and the viewer is the post's author (user 3). The self-like check fires first and sets `DISABLE=1` with `ACTION_ON_CLICK='CANT_LIKE_OWN_POST'`. Because the user *does* have permission and is not anonymous, the second disable check does not overwrite this message. |

**Fixture:** `tests/event/fixtures/users.xml` — 4 topics, 4 posts all by poster_id=3, 6 likes in `posts_likes`, 2 users (ids 2 and 3). User 4 appears in `posts_likes` but has no `phpbb_users` row — this is intentional for the JOIN exclusion test in 3.4.

---

#### 4. `tests/event/summary_event_test.php`

**What this code does:** The `summary_listener` class powers two features:

1. **Most-liked posts panels** — optional widgets that can appear on the board index and on viewforum pages, showing the most-liked posts for configurable time periods (today, this week, this month, this year, all time). Each period is independently enabled and has its own result limit.

2. **Per-topic like counts on viewforum** — a small heart + number shown next to each topic title on the forum topic list, showing how many total likes all the posts in that topic have received.

**Time-period filtering:** The fixture uses likes with timestamps from 2017. The test clock is pinned to Unix timestamp `1500000000` (2017-07-14) so that "this year", "this month", "this week", and "today" boundaries are fixed and the expected result counts are deterministic regardless of when the tests are run.

**Index page tests** (`test_index_modify_page_title`) — shows most-liked posts across all forums the viewer can read:

| # | Data Set | Scenario | Expected Posts |
|---|----------|----------|----------------|
| 4.1 | `show all` | Registered user, all-time limit of 10, both forums readable | 5 |
| 4.2 | `show all only in Forum 1` | Same, but viewer cannot read forum 2 — its post is excluded | 4 |
| 4.3 | `anonymous user` | Not logged in, all periods enabled with limit 1 each — deduplication means only 3 distinct posts reach the panel | 3 |
| 4.4 | `only this year` | Only the this-year bucket is enabled. Likes from 2016 and earlier fall outside the window. | 3 |
| 4.5 | `only this month` | Only likes from July 2017 qualify | 2 |
| 4.6 | `only this week` | Only likes from the week of 2017-07-10 qualify | 2 |
| 4.7 | `only today` | Only likes from 2017-07-14 qualify | 1 |
| 4.8 | `none at all` | All period limits are 0 — the panel is empty | 0 |

**Forum page tests** (`test_viewforum_modify_page_title`) — same matrix but scoped to forum 1 and its sub-forums, so forum 2 posts never appear regardless of permissions:

| # | Data Set | Scenario | Expected Posts |
|---|----------|----------|----------------|
| 4.9 | `show all` | All time, forum 1 with sub-forums | 4 |
| 4.10 | `show all only in Forum 1` | Same — forum 2 already excluded by forum scope | 4 |
| 4.11 | `anonymous user` | Anonymous, 1 per period | 3 |
| 4.12 | `only this year` | Year period only | 3 |
| 4.13 | `only this month` | Month period only | 2 |
| 4.14 | `only this week` | Week period only | 2 |
| 4.15 | `only today` | Today period only | 1 |
| 4.16 | `none at all` | All limits = 0 | 0 |

**Viewforum topic row tests** — the per-topic heart counts on the topic list:

| # | Test | Scenario | Expected |
|---|------|----------|----------|
| 4.17 | `test_prefetch_topic_likes` | Before the topic list renders, a batch query counts all likes across every post in each topic. The internal `$topic_like_counts` cache is read via reflection and verified: topic 1 has 4 likes (posts 1, 3, and 4 combined), topic 2 has 1, topic 3 has 1. | `[1=>4, 2=>1, 3=>1]` |
| 4.18 | `test_inject_topic_like_count` | For each topic row the cached count is written into the template data as `TOPIC_LIKE_COUNT`. Tests three rows: a topic with many likes, a topic with one like, and a topic ID that was not in the prefetch (must default to 0 rather than crash). | 4, 1, and 0 respectively |

**Fixture:** `tests/event/fixtures/summary_data.xml` — 2 forums, 3 topics, 5 posts, 6 likes with carefully chosen timestamps. The test clock is pinned to Unix timestamp 1500000000 (2017-07-14 02:40 UTC) so period boundaries are always in the same place.

---

#### 5. `tests/service/topic_likes_test.php`

**What this code does:** `service/topic_likes.php` is a small public service that other extensions can use to get like counts per topic without touching the `posts_likes` table directly. For example, a "recent topics" extension could show a Likes column using this service. It is registered in phpBB's dependency injection container as `avathar.postlove.topic_likes` and declared as an optional dependency (`@?`) so that if Post Love is not installed, the consuming extension just receives `null` and can skip the feature gracefully.

**What the tests check:** The one public method — `get_topic_like_counts(array $topic_ids): array` — across three important edge cases.

| # | Test | Scenario | Expected |
|---|------|----------|----------|
| 5.1 | `test_empty_input` | Calling the method with an empty array should return immediately without running any database query. This is important because consumers often call it with `array_keys($topic_list)` which may be empty on a page with no topics. | `[]` |
| 5.2 | `test_counts` | Pass topic IDs 1, 2, and 3 which all exist in the fixture with known like counts. Verifies the SQL aggregation is correct: topic 1 has posts 1, 3, and 4 (4 total likes across them), topic 2 has 1, topic 3 has 1. | `[1 => 4, 2 => 1, 3 => 1]` |
| 5.3 | `test_missing_topic_absent` | Pass topic 2 (1 like) and topic 99 (does not exist). The method must not return topic 99 as `99 => 0` — it simply omits topics that have no likes. Consumers must use `$counts[$id] ?? 0` rather than `$counts[$id]`. | `[2 => 1]`, key 99 absent |

**Fixture:** Reuses `tests/event/fixtures/summary_data.xml`.

---

### Functional Tests

Functional tests run against a full live phpBB installation. They use a real browser-like HTTP client (`GuzzleHttp`) to make requests, follow redirects, parse HTML, and fill in forms. They are slower than unit tests and require the phpBB test environment to be installed and running — either locally (see the Test Framework section above) or via the GitHub Actions workflow.

---

#### 6. `tests/functional/postlove_acp_test.php`

**What this code does:** The extension adds a configuration page to phpBB's Admin Control Panel (ACP) where admins can enable or disable features like the heart button, the like counters in post profiles, and the most-liked summary panels.

**What the test checks:** That the ACP page loads without a PHP error and contains the expected configuration form. It is a smoke test — it does not verify that saving the form actually changes anything; that is covered by test 7.4.

| # | Test | Scenario |
|---|------|----------|
| 6.1 | `test_acp_pages` | Log in as admin, navigate to the Post Love ACP module, confirm the form renders and contains the `POSTLOVE_SHOW_LIKES` and `POSTLOVE_SHOW_LIKED` language keys |

---

#### 7. `tests/functional/postlove_post_test.php`

**What this code does:** These are end-to-end tests that exercise the like feature through the actual forum UI. Each test creates or reuses real posts on the board and verifies what the browser sees.

Post Love can display the like button in two visual modes, controlled by an ACP setting:
- **Inline mode** (`button_mode=0`): a small heart appears directly under the post text, styled via CSS.
- **Button mode** (`button_mode=1`): the heart appears in the post action bar alongside Quote and Edit, matching phpBB's default button style.

The tests switch between these modes to confirm both render correctly.

| # | Test | Scenario |
|---|------|----------|
| 7.1 | `test_post` | Creates a topic and a reply post. Switches to inline mode, verifies the heart element exists, toggles a like via AJAX, reloads the page, and confirms the count shows "1". Toggles unlike to clean up. Switches to button mode and repeats the like/verify cycle. |
| 7.2 | `test_guest_see_loves` | Without logging in, loads the topic page and verifies the like count ("1" from test 7.1) is visible in both display modes. Guests should always be able to see counts even though they cannot like. |
| 7.3 | `test_guests_cannot_like` | As a guest, sends the AJAX like request. The controller should reject it. Reloads the page and confirms the count is still "1" — the guest request had no effect. |
| 7.4 | `test_show_likes_given` | Exercises all 4 combinations of the "show likes given" and "show likes received" ACP toggles (off/off, on/off, off/on, on/on). For each, saves the ACP form, loads a topic, and checks whether the `.like_info` and `.liked_info` elements appear in the post profile area. |
| 7.5 | `test_show_list` | Loads the love list page for user 2 (`/postlove/2`) and confirms exactly one liked post appears in the list. |

**Helper:** `tests/functional/CssParser.php` — parses CSS files to support button mode display assertions.
**Base class:** `tests/functional/postlove_base.php` — provides shared helper methods used by both functional test classes: `set_button_mode()`, `show_likes()`, `show_liked()`, and `get_topic_id()`.

---

## Fixtures

All XML fixtures follow phpBB's DbUnit format. The test runner loads the file, truncates the relevant tables, and inserts the rows before each test. Key tables used:

- **`phpbb_users`** — test users. Includes a `username_clean` column required by a MySQL unique index. User IDs deliberately do not start at 1 (which phpBB reserves for the anonymous user).
- **`phpbb_posts`** / **`phpbb_topics`** / **`phpbb_forums`** — the posts, topics, and forums that likes point to.
- **`phpbb_posts_likes`** — pre-seeded like records. The `liketime` column holds Unix timestamps; the summary tests use timestamps from 2017 so that period filters (today/week/month/year) behave predictably when the test clock is pinned.
