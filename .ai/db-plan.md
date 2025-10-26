### Tabela: users
```
id (primary key)
email (unique)
password (hashed)
created_at
updated_at
```

### Tabela: events
```
id (primary key)
user_id (foreign key → users)
name (string, required)
date (datetime, required)
slug (unique, for guest URL)
gallery_published (boolean, default false)
gallery_slug (unique, for gallery URL, nullable)
created_at
updated_at
```

### Tabela: guests
```
id (primary key)
event_id (foreign key → events)
name (string, required)
session_id (string, unique - z localStorage)
created_at
updated_at
```

### Tabela: photos
```
id (primary key)
event_id (foreign key → events)
guest_id (foreign key → guests)
filename (UUID v6)
uploaded_at (timestamp)
deleted_at (soft delete, nullable)
```

### Relacje
- User → Events (1:N)
- Event → Photos (1:N)
- Event → Guests (1:N)
- Guest → Photos (1:N)