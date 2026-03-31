# Post Love Extension — Events & Integration Points

## 1. Own Events & API (emitted by this extension)

This section is the public API contract. These are the events and services that Post Love deliberately exposes so that *other* extensions can integrate with it. If you are building an extension and want to add data to the most-liked summary panel, or query like counts without touching the database yourself, this is where to look. Changing anything listed here is a breaking change and requires a major version bump.

### 1.1 PHP Events

#### `avathar.postlove.modify_summary_tpl_ary`

Modify or add template variables for a post row in the most-liked summary panel just before it is assigned to the template. Use this to inject extra display data per row (e.g. avatars, reputation scores, custom badges).

- **Placement:** `event\summary_listener::topposts_of_period()`
- **Since:** 2.2.2
- **Arguments:**
  - `row` (array) — Raw post/topic data from the query (`post_id`, `topic_id`, `forum_id`, `post_time`, `user_id`, `username`, `user_colour`, `topic_title`, `forum_name`, `sum_likes`)
  - `tpl_ary` (array) — Template block array about to be assigned to `most_liked_posts`
- **Known listeners:** none

### 1.2 Public Service API

#### `avathar.postlove.topic_likes`

Provides aggregated like counts per topic without requiring direct database access.

- **Class:** `avathar\postlove\service\topic_likes`
- **DI reference:** `@?avathar.postlove.topic_likes` (nullable — gracefully absent when postlove is not installed)
- **Method:** `get_topic_like_counts(array $topic_ids): array`
  - Returns `[topic_id => like_count]`
- **Known consumers:** `avathar/recenttopicsav` — displays a Likes column per topic in the recent topics listing

**Example `services.yml`:**
```yaml
my_extension.my_service:
    class: my_vendor\my_ext\my_service
    arguments:
        - '@?avathar.postlove.topic_likes'
```

**Example usage:**
```php
if ($this->topic_likes !== null)
{
    $counts = $this->topic_likes->get_topic_like_counts($topic_ids);
    // $counts = [42 => 7, 99 => 3, ...]
}
```

### 1.3 Routes

| Route name | Path | Purpose |
|---|---|---|
| `avathar_postlove_control` | `/postlove/toggle/{post_id}` | AJAX like/unlike toggle endpoint |
| `avathar_postlove_list` | `/postlove/{user_id}` | User love list page (page 1) |
| `avathar_postlove_list_page` | `/postlove/{user_id}/page/{page}` | User love list page (paginated) |

### 1.4 Permissions

| Permission key | Category | Purpose |
|---|---|---|
| `u_postlove` | misc | Allow user to like/unlike posts |
| `u_postlove_summary` | misc | Allow user to see the most-liked posts summary panels |

---

## 2. Events Subscribed from Other Extensions

Extensions can listen to each other's events or consume each other's services. This section documents every place where Post Love reaches *out* to another extension — for example, calling a service provided by a neighbouring extension when it is installed. These integrations are always optional (soft-coupled): Post Love works normally when the other extension is absent.

None. Post Love does not subscribe to any events dispatched by third-party extensions.

---

## 3. phpBB Core Events & Template Events

phpBB itself fires hundreds of named events at key moments — when a post is rendered, when a page loads, when a user is deleted, and so on. Extensions hook into these events without modifying any phpBB core files.

**PHP events** are fired from within phpBB's PHP code. Your extension subscribes to them by registering a listener class (implementing `EventSubscriberInterface`). When the event fires, phpBB passes a data object containing variables you can read and write — for example, modifying a SQL query array before it is executed, or adding a variable to the template data for a post row. PHP events are the right tool when you need to run logic, query the database, or compute something.

**Template events** are fired from within phpBB's HTML templates. Your extension hooks into them simply by creating an HTML file whose name matches the event, placed at `styles/.../template/event/<event_name>.html`. phpBB automatically includes that file at the event location when rendering the page. Template events are the right tool when you only need to inject markup — a button, a counter, a link — at a fixed point in the page layout, without any PHP logic.

This section lists every phpBB hook that Post Love uses internally to deliver its functionality.

### 3.1 PHP Events — Main listener (`event/main_listener.php`)

| phpBB Core Event | Handler | Purpose |
|---|---|---|
| `core.permissions` | `add_permissions()` | Register `u_postlove` and `u_postlove_summary` permissions |
| `core.user_setup` | `load_language_on_setup()` | Load the postlove language file on every page |
| `core.viewtopic_modify_post_data` | `prefetch_likes()` | Batch-prefetch all like data for posts on the page (1–3 queries total) |
| `core.viewtopic_modify_post_row` | `modify_post_row()` | Inject heart button, like count, tooltip, and mini-profile counters into each post row |
| `core.memberlist_view_profile` | `user_profile_likes()` | Add love list link to member profile statistics section |
| `core.delete_posts_after` | `clean_posts_after()` | Remove likes referencing permanently deleted posts |
| `core.delete_user_after` | `clean_users_after()` | Remove likes given by permanently deleted users |

### 3.2 PHP Events — Summary listener (`event/summary_listener.php`)

| phpBB Core Event | Handler | Purpose |
|---|---|---|
| `core.index_modify_page_title` | `index_page_summary()` | Render most-liked posts summary panel on the board index |
| `core.viewforum_modify_page_title` | `forum_page_summary()` | Render most-liked posts summary panel on viewforum pages |
| `core.viewforum_modify_topics_data` | `prefetch_topic_likes()` | Batch-prefetch like counts for all topics on the current viewforum page |
| `core.viewforum_modify_topicrow` | `inject_topic_like_count()` | Inject `TOPIC_LIKE_COUNT` into each topic row on viewforum |

### 3.3 Template Events

| Template Event | Style scope | Purpose |
|---|---|---|
| `overall_header_head_append` | all | Inject postlove CSS |
| `overall_footer_after` | all | Inject postlove JS |
| `viewtopic_body_postrow_custom_fields_after` | all | Inject like count + heart button inline below post (inline display mode) |
| `viewtopic_body_postrow_post_notices_after` | all | Inject mini-profile likes given/received counters |
| `viewtopic_body_post_buttons_after` | prosilver | Inject heart button in post action bar (button display mode) |
| `topiclist_row_append` | prosilver | Inject total like count per topic on viewforum topic list |
| `viewforum_body_topic_row_before` | prosilver | Inject most-liked summary panel above/below the topic list |
| `forumlist_body_category_header_before` | prosilver | Inject most-liked summary panel on board index |
| `index_body_forumlist_body_after` | prosilver | Inject most-liked summary panel below forumlist on index |
| `memberlist_view_user_statistics_after` | all | Inject love list link in member profile statistics |
