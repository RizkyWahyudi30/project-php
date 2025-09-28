<?php
// buat nama konstanta --> "TASK_FILE" yang merujuk ke file "tasks.json" di folder yg sama dengan "task.php"
define("TASK_FILE", __DIR__ . "/tasks.json");


// membuat function untuk mengecek apakah tasks.json sudah ada
function loadTasks()
{
    // jika tasks.json tidak ada, maka return array kosong
    if (!file_exists(TASK_FILE)) {
        return [];
    }

    // kalau ada, maka akan dibaca dengan method "file_get_constents"
    $data = file_get_contents(TASK_FILE);
    // dan kembalikan json dengan berbentuk array dengan json_decode
    return json_decode($data, true) ?? [];
    // breakdown the syntax:
    /**
     * 
     * json_decode($data, true) ?? [] --> artinya coba ngubah data JSON menjadi assosiative array (with key-values), jika decode berhasil dari $data(TASK_FILE), maka akan menampilkan hasil array nya. Jika tidak, maka akan mengembalikkan nilai null dan array kosong
     */
}

// membuat function saveTasks --> untuk menyimpan hasil array $tasks ke file tasks.json dalam format JSON
function saveTasks($tasks)
{
    file_put_contents(TASK_FILE, json_encode($tasks, JSON_PRETTY_PRINT));
    // breakdown syntax :
    /**
     * file_put_contents(TASK_FILE, json_encode($tasks, JSON_PRETTY_PRINT));
     * method built-in file_put_contents mengambil data dari constant TASK_FILE, lalu array json nya dsimpan di variable $tasks dengan format JSON yang rapi
     * 
     */
}

// ===========================
// CRUD 
// function addTask
function addTask($description) // parameter $description
{
    $tasks = loadTasks(); // variable ini akan menyimpan data dan dikirimkan ke function loadTasks agar data diolah menjadi assoasiative array
    $id = count($tasks) > 0 ? max(array_column($tasks, "id")) + 1 : 1; // variable $id akan mengitung data dari variable $tasks lalu meeriksa apakah data tidak kosong, jika tidak, maka data akan diperiksa dan dicari nilai data tertinggi dengan max() lalu array_column() berguna untuk mengambil nilai dari kolom "id" dari setiap data di $tasks dan mengembalikkan menjadi array baru 
    // lalu tambah 1 di nilai tertinggi dan mengembalikan Id baru, jika $tasks tidak / kosong, maka akan ditambahkan 1 untuk Id pertama 
    $now = date("Y-m-d H:i:s"); // format penanggalan yang akan dikembalikkan 

    $tasks[] = [ // menambahkan elemen baru ke array asosiatif 
        "id" => $id,
        "description" => $description,
        "status" => "todo",
        "createAt" => $now,
        "updateAt" => $now
    ];

    saveTasks($tasks); // saveTasks() berguna untuk menyimpan segala proses add yang ada di variable $tasks
    echo "✅ Task ditambahkan (Id: $id)" . PHP_EOL;

    // breakdown code:
}

// Update tasks
function updateTask($id, $description)
{
    $tasks = loadTasks(); // memanggil function loadTask() untuk mengelola semua data di dalam 
    foreach ($tasks as &$task) {
        // mengecek apakah di setiap iterasi, akan memeriksa apakah nilai id ada yang sama dengan $id
        if ($task["id"] == $id) {
            // jika Id cocok, maka deskripsi akan dirubah sesuai $description yang baru
            $task["description"] = $description;
            // mencatat perubahan waktu ketika ditambahkan atau diperbarui 
            $task["updateAt"] = date("Y-m-d H:i:s");
            // setelah semua sudah diperbarui, saveTask menyimpan di variable $tasks lalu dikirim ke function loadTask()
            saveTasks($tasks);

            echo "✏️  Task $id berhasil di update" . PHP_EOL;
            return; // perulangan dihentikan setetalh data ditemukan dan diperbarui
        }
    }
    // jika perulangan selesai tanpa menemukan ID yang cocok
    echo "⚠️ Task $id tidak ditemukan" . PHP_EOL;
}

// Delete task
function deleteTask($id)
{
    $tasks = loadTasks();
    // fungsi ini menggunakan array_filter() untuk memfilter dari variable $tasks, arrow function akan memeriksa jika id dari data tidak sama dengan $id yang ingin dihapus 
    $newTasks = array_filter($tasks, fn($t) => $t['id'] != $id);

    // mengecek jika jumlah data dari $tasks dan jumlah data dari $newTasks sama, maka berarti tidak ada yang dihapus , ini terjadi jika Id data yang diberikan tidak ditemukan
    if (count($tasks) == count($newTasks)) {
        echo "⚠️ Task $id tidak ditemukan" . PHP_EOL;
        return;
    }

    // menyimpan hasil data dari perubahan array $newTasks
    saveTasks(array_values($newTasks));
    echo "🗑️ Task $id berhasil dihapus" . PHP_EOL;
}

// function change status task
function markTask($id, $status)
{
    $tasks = loadTasks();
    foreach ($tasks as &$task) {
        if ($task['id'] == $id) {
            $task['status'] = $status;
            $task['updateAt'] = date("Y-m-d H:i:s");

            saveTasks($tasks);
            echo "🔖 Task $id ditandai sebagai $status" . PHP_EOL;
            return;
        }
    }
    echo "⚠️ Task $id tidak ditemukan" . PHP_EOL;
}

// function list task
function listTask($filter = null)
{
    $tasks = loadTasks();
    if ($filter) {
        $tasks = array_filter($tasks, fn($t) => $t['status'] === $filter);
    }

    if (empty($tasks)) {
        echo "📭 Tidak ada task" . PHP_EOL;
        return;
    }

    foreach ($tasks as $task) {
        echo "[{$task['id']}] {$task['description']} ({$task['status']})" . PHP_EOL;
    }
}


// ==== Main CLI ====
$args = $_SERVER['argv'];
array_shift($args);

if (count($args) === 0) {
    echo "Usage: PHP tasks.php [command] [arguments]" . PHP_EOL;
    exit;
}
$command = $args[0];

switch ($command) {
    case "add":
        if (!isset($args[1])) {
            echo "⚠️ Deskripsi task harus diisi" . PHP_EOL;
            exit;
        }
        addTask($args[1]);
        break;
    case "update":
        if (count($args) < 3) {
            echo "⚠️ Gunakan: php tasks.php update [id] [deskripsi]" . PHP_EOL;
            exit;
        }
        updateTask((int) $args[1], $args[2]);
        break;
    case "delete":
        if (!isset($args[1])) {
            echo "⚠️ Gunakan: php tasks.php delete [id]" . PHP_EOL;
            exit;
        }
        deleteTask((int) $args[1]);
        break;
    case "mark-in-progress":
        if (!isset($args[1])) {
            echo "⚠️ Gunakan: php taskS.php mark-in-progress [id]" . PHP_EOL;
            exit;
        }
        markTask((int) $args[1], "in-progress");
        break;
    case "mark-done":
        if (!isset($args[1])) {
            echo "⚠️ Gunakan: php tasks.php mark-done [id]\n";
            exit;
        }
        markTask((int) $args[1], "done");
        break;
    case "list":
        $filter = $args[1] ?? null;
        listTask($filter);
        break;
    default:
        echo "⚠️ Command tidak dikenal: $command" . PHP_EOL;
}
