### Tabela: users
```
id (UUID primary key)
email (unique)
password (hashed)
created_at
updated_at
```

### Tabela: events
```
id (UUID primary key, used for guest app URL)
user_id (UUID foreign key → users)
name (string, required)
date (datetime, required)
created_at
updated_at
```

### Tabela: guests
```
id (UUID primary key)
event_id (UUID foreign key → events)
name (string, required)
session_id (string, unique - z localStorage)
created_at
updated_at
```

### Tabela: photos
```
id (UUID primary key)
event_id (UUID foreign key → events)
guest_id (UUID foreign key → guests)
filename (UUID v6)
taken_at (timestamp, nullable)
created_at
updated_at
deleted_at (soft delete, nullable)
```

### Relacje
- User → Events (1:N)
- Event → Photos (1:N)
- Event → Guests (1:N)
- Guest → Photos (1:N)