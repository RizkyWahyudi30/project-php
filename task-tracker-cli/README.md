## Task Tracker CLI

// Struktur folder
task-tracker/
│── task.php # file utama CLI
│── tasks.json # file penyimpanan (otomatis dibuat)

Command :

# Tambah task

php task.php add "Belajar PHP CLI"

# Lihat semua task

php task.php list

# Update task

php task.php update 1 "Belajar PHP & JSON"

# Tandai selesai

php task.php mark-done 1

# List hanya yang selesai

php task.php list done

# Hapus task

php task.php delete 1

https://roadmap.sh/projects/task-tracker
