
# BE (Laravel) | Coding Challenge - Programmer

Aplikasi yang memungkinkan pencatatan pekerjaan pegawai, lengkap dengan
perhitungan remunerasi. 


## FAQ

#### Arsitektur Solusi:
```bash
[employees]                          [tasks]
+------------+                      +---------------------+
| id (PK)    |<---------------+     | id (PK)             |
| name       |                |     | description         |
| created_at |                +-----| date                |
| updated_at |                      | hourly_rate         |
+------------+                      | additional_charges  |
                                    | total_remuneration  |
                                    | created_at          |
                                    | updated_at          |
                                    +---------------------+
                                           ▲
                                           |
                                           |
                                   [task_assignments]
                                   +------------------------+
                                   | id (PK)               |
                                   | task_id (FK → tasks)  |
                                   | employee_id (FK → emp)|
                                   | hours_spent           |
                                   | total                 |
                                   | created_at            |
                                   | updated_at            |
                                   +------------------------+
```

#### Penjelasan Desain

TASK:
Input: Menginputkan semua Assignments saat proses input, agar lebih mudah untuk perhitangan datanya.

```json
{
    "description": "Develop landing page",
    "date": "2025-05-23",
    "hourly_rate": 5000,
    "additional_charges": 0,
    "assignments": [
        {
            "employee_id": 1,
            "hours_spent": 0.5
        },
        {
            "employee_id": 2,
            "hours_spent": 6
        }
    ]
}
```
Perhitungan:
mengakumulasikan semua data dari assignments
```php
$total = ($assignment['hours_spent'] * $task->hourly_rate) + $task->additional_charges;
```

Setelah itu baru mengupdate `task` yang telah diinput. 

```php
$totalRemuneration = collect($task->taskAssignments)->sum('total');
$task->update(['total_remuneration' => $totalRemuneration]);
```


#### Setup & Deploy:

```bash
  composer install
  cp .env.example .env
  php artisan key:generate
  php artisan jwt:secret
  php artisan migrate
```

#### Tantangan & Solusi:

Sejauh ini untuk backend semuanya aman, yang lumyan stuggle di frontend.
