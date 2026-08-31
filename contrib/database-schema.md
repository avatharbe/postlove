# Post Love Extension — Database Schema

The extension's own table is `phpbb_posts_likes`. Everything else here is a
core phpBB table (or, for `phpbb_thanks`, another extension's table) that
Post Love reads from or writes to. Columns are abbreviated to what Post Love
actually touches — see the linked source for the full column list of any
core table.

```mermaid
erDiagram
    phpbb_posts_likes {
        int post_id PK "FK -> phpbb_posts"
        int user_id PK "FK -> phpbb_users, the liker"
        string type
        int liketime "indexed"
        int liked_user_id FK "indexed, denormalized poster copy"
    }

    phpbb_posts {
        int post_id PK
        int poster_id FK
        int topic_id FK
        int forum_id FK
    }

    phpbb_topics {
        int topic_id PK
        string topic_title
        int topic_status
        int forum_id FK
    }

    phpbb_forums {
        int forum_id PK
        string forum_name
        int parent_id FK "self, sub-forums"
        string forum_password
    }

    phpbb_forums_access {
        int forum_id FK
        string session_id "the password-unlock record"
        int user_id FK
    }

    phpbb_users {
        int user_id PK
        string username
        int user_type
    }

    phpbb_thanks {
        int post_id "Thanks for Posts extension"
        int user_id "the thanker"
        int poster_id
        int thanks_time
    }

    phpbb_profile_fields_data {
        int user_id PK
        boolean pf_postlove_hide "the opt-out flag"
    }

    phpbb_notifications {
        int notification_type_id "generic, one row per type"
        int item_id "= post_id"
        int item_parent_id "= liker"
        int user_id "= poster, recipient"
    }

    phpbb_posts_likes }o--|| phpbb_posts        : "post_id"
    phpbb_posts_likes }o--|| phpbb_users        : "user_id (liker)"
    phpbb_posts_likes }o--|| phpbb_users        : "liked_user_id (poster copy)"
    phpbb_posts        }o--|| phpbb_users        : "poster_id (source of truth)"
    phpbb_posts        }o--|| phpbb_topics       : "topic_id"
    phpbb_posts        }o--|| phpbb_forums       : "forum_id"
    phpbb_forums        ||--o{ phpbb_forums       : "parent_id (sub-forums)"
    phpbb_forums_access }o--|| phpbb_forums       : "forum_id"
    phpbb_forums_access }o--|| phpbb_users        : "user_id (unlocked by)"
    phpbb_thanks        }o..o{ phpbb_posts_likes  : "importThanks(): INSERT...SELECT"
    phpbb_profile_fields_data ||--|| phpbb_users  : "user_id (pf_postlove_hide)"
    phpbb_notifications }o..o{ phpbb_posts        : "item_id = post_id"
    phpbb_notifications }o..o{ phpbb_users        : "item_parent_id / user_id"
```

## Notes

- **`liked_user_id`** is a denormalized snapshot of `phpbb_posts.poster_id`,
  backfilled once by `release_2_0_0_add_liked_user_id` so "likes received"
  counts don't need a join to `phpbb_posts`. It is kept in sync by
  `main_listener::clean_users_after()` on `core.delete_user_after`, and
  reconciled by the ACP's repair tool (`acp_postlove_module::cleanPostLoves()`)
  for any rows that predate that listener.
- **`phpbb_forums_access`** is what `service\forum_access::drop_locked()`
  checks before showing anything derived from a forum's contents — `f_read`
  is granted independently of a forum password, so it alone is not enough to
  know whether the viewer may actually see the forum.
- **`phpbb_thanks`** is not a live foreign key relationship — `importThanks()`
  runs a one-off `INSERT ... SELECT` from it into `phpbb_posts_likes`, guarded
  by `sql_table_exists()` since the Thanks for Posts extension may not be
  installed.
- **`phpbb_notifications`** is a generic, polymorphic core table shared by
  every notification type. phpBB dedupes by `(notification_type_id, item_id)`
  alone, so a liked post has at most one notification row, owned by whoever
  liked it first — see `notifyhelper::notify()`.
