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
| `avathar_postlove_control` | `/postlove/{action}/{post}` | AJAX like/unlike toggle endpoint (`action=toggle`, `post=<post_id>`) |
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
