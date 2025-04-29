<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Полезные ссылки</title>
    <style>
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .link {
            text-align: center;
            margin-bottom: 20px;
            text-decoration: none;
            color: #000;
            font-size: 2.2em;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .card {
            position: relative;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card a {
            text-decoration: none;
            color: #1a73e8;
            font-size: 1.2em;
        }
        .card p {
            color: #666;
            margin: 10px 0;
        }
        .menu-btn {
            opacity: 80%;
            border-radius: 50px;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #666;
            position: absolute;
            right: 10px;
            top: 10px;
        }
        .menu-option {
            display: block;
            width: 100%;
            padding: 10px 20px;
            background: white;
            border: none;
            text-align: left;
            cursor: pointer;
            font-size: 14px;
        }
        .menu-option:hover {
            background: #f0f0f0;
        }
        .sortable-ghost {
            opacity: 0.4;
        }

        /* --- Modal styles --- */
        .modal {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
            z-index: 100;
        }
        .modal-content {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            width: 90%;
            max-width: 400px;
        }
        .modal-actions {
            text-align: right;
            margin-top: 15px;
        }
        .modal-actions button {
            margin-left: 10px;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .modal-actions button[type="submit"],
        #confirmDelete {
            background: #1a73e8;
            color: #fff;
        }
        .modal-actions button#cancelDelete,
        .modal-actions button#cancelEdit,
        .modal-actions button[type="button"] {
            background: #ccc;
            color: #333;
        }
        .form-group {
            margin-bottom: 10px;
        }
        .form-group label {
            display: block;
            margin-bottom: 4px;
        }
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 6px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="admin.php" class="link">Админ-панель</a>
        <div class="grid">
            <?php
            $stmt = $pdo->query("SELECT * FROM links ORDER BY position ASC, created_at DESC");
            while ($row = $stmt->fetch()):
                $url = htmlspecialchars($row['url']);
                $title = htmlspecialchars($row['title']);
                $description = htmlspecialchars($row['description']);
                $created_at = $row['created_at'];
                $youtube_thumbnail = '';
                if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^\&\?\/]+)/', $url, $m)) {
                    $youtube_thumbnail = "https://img.youtube.com/vi/{$m[1]}/maxresdefault.jpg";
                }
            ?>
            <div class="card" data-id="<?= $row['id'] ?>">
                <button class="menu-btn">⋯</button>
                <div class="menu-options" style="display:none; position:absolute; right:10px; top:30px; background:white; box-shadow:0 2px 8px rgba(0,0,0,0.2); border-radius:6px; overflow:hidden; z-index:10;">
                    <button class="menu-option delete">Șterge</button>
                    <button class="menu-option edit">Editează</button>
                </div>

                <?php if ($youtube_thumbnail): ?>
                    <img src="<?= $youtube_thumbnail ?>" alt="Thumbnail" style="width:100%; border-radius: 8px 8px 0 0;">
                <?php endif; ?>

                <a href="<?= $url ?>" target="_blank"><?= $title ?></a>
                <p><?= $description ?></p>
                <small><?= $created_at ?></small>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Modale -->
    <div id="deleteModal" class="modal">
      <div class="modal-content">
        <p>Sigur vrei să ștergi acest link?</p>
        <div class="modal-actions">
          <button id="confirmDelete">Da, șterge</button>
          <button id="cancelDelete">Anulează</button>
        </div>
      </div>
    </div>

    <div id="editModal" class="modal">
      <div class="modal-content">
        <h3>Editare link</h3>
        <form id="editForm">
          <input type="hidden" name="id" id="editId">
          <div class="form-group">
            <label>Titlu:</label>
            <input type="text" name="title" id="editTitle" required>
          </div>
          <div class="form-group">
            <label>URL:</label>
            <input type="url" name="url" id="editUrl" required>
          </div>
          <div class="form-group">
            <label>Descriere:</label>
            <textarea name="description" id="editDescription"></textarea>
          </div>
          <div class="modal-actions">
            <button type="submit">Salvează</button>
            <button type="button" id="cancelEdit">Anulează</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Bibliotecă SortableJS -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

    <script>
    // Meniu ⋯
    document.querySelectorAll('.menu-btn').forEach(btn => {
      btn.addEventListener('click', e => {
        e.stopPropagation();
        const opts = btn.nextElementSibling;
        opts.style.display = opts.style.display === 'block' ? 'none' : 'block';
      });
    });
    document.addEventListener('click', () => {
      document.querySelectorAll('.menu-options').forEach(m => m.style.display = 'none');
    });

    // Ștergere prin modal
    let deleteTarget = null;
    document.querySelectorAll('.delete').forEach(btn => {
      btn.addEventListener('click', e => {
        e.stopPropagation();
        deleteTarget = btn.closest('.card');
        document.getElementById('deleteModal').style.display = 'flex';
      });
    });
    document.getElementById('confirmDelete').addEventListener('click', () => {
      const id = deleteTarget.getAttribute('data-id');
      fetch('delete_link.php', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: 'id='+encodeURIComponent(id)
      }).then(r=>r.json()).then(data=>{
        if(data.success) deleteTarget.remove();
        else alert('Eroare la ștergere.');
        document.getElementById('deleteModal').style.display = 'none';
      });
    });
    document.getElementById('cancelDelete').addEventListener('click', () => {
      document.getElementById('deleteModal').style.display = 'none';
    });

    // Editare prin modal
    document.querySelectorAll('.edit').forEach(btn => {
      btn.addEventListener('click', e => {
        e.stopPropagation();
        const card = btn.closest('.card');
        document.getElementById('editId').value = card.dataset.id;
        document.getElementById('editTitle').value = card.querySelector('a').innerText;
        document.getElementById('editUrl').value = card.querySelector('a').href;
        document.getElementById('editDescription').value = card.querySelector('p').innerText;
        document.getElementById('editModal').style.display = 'flex';
      });
    });
    document.getElementById('cancelEdit').addEventListener('click', () => {
      document.getElementById('editModal').style.display = 'none';
    });
    document.getElementById('editForm').addEventListener('submit', e => {
      e.preventDefault();
      const form = new FormData(e.target);
      fetch('edit_link.php', { method:'POST', body: form })
        .then(r=>r.json()).then(data=>{
          if(data.success) {
            const id = form.get('id');
            const card = document.querySelector(`.card[data-id="${id}"]`);
            card.querySelector('a').innerText = form.get('title');
            card.querySelector('a').href = form.get('url');
            card.querySelector('p').innerText = form.get('description');
          } else alert('Eroare la editare.');
          document.getElementById('editModal').style.display = 'none';
        });
    });

    // Drag & drop + salvare ordine
    const grid = document.querySelector('.grid');
    Sortable.create(grid, {
      animation: 150,
      ghostClass: 'sortable-ghost',
      onEnd: () => {
        const order = [];
        document.querySelectorAll('.card').forEach((c,i) => {
          order.push({ id: c.dataset.id, position: i });
        });
        fetch('update_order.php', {
          method: 'POST',
          headers: {'Content-Type':'application/json'},
          body: JSON.stringify(order)
        }).then(r=>r.json()).then(d=>{
          if(!d.success) alert('Eroare la salvarea ordinii!');
        });
      }
    });
    </script>
</body>
</html>
