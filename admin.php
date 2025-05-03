<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Админ-панель</title>
  <style>
    * { box-sizing: border-box; margin:0; padding:0; }
    body { font-family: Arial, sans-serif; background:#f0f2f5; color:#333; }

    .container { max-width:900px; margin:40px auto; padding:0 20px; }
    h2 { margin-bottom:20px; font-size:1.5em; color:#1a73e8; }

    .admin-form {
      background:#fff; border-radius:8px; padding:20px;
      box-shadow:0 2px 8px rgba(0,0,0,0.1); margin-bottom:40px;
    }
    .form-group { margin-bottom:15px; }
    label { display:block; margin-bottom:6px; font-weight:600; }
    input, textarea {
      width:100%; padding:10px; border:1px solid #ccc; border-radius:4px;
      font-size:0.95em;
    }
    button[type=submit] {
      background:#1a73e8; color:#fff; border:none;
      padding:10px 16px; border-radius:4px; cursor:pointer;
      font-size:1em;
    }
    #message {
      margin-top:15px; padding:10px; border-radius:4px; display:none;
    }

    .grid {
      display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr));
      gap:20px; margin-bottom:40px;
    }
    .card {
      background:#fff; border-radius:8px; overflow:hidden;
      box-shadow:0 2px 8px rgba(0,0,0,0.07); position:relative;
      transition:transform .2s;
    }
    .card:hover { transform:translateY(-4px); }

    .card-content { padding:16px; }
    .card-content strong { display:block; margin-bottom:8px; font-size:1.1em; }
    .card-content p { font-size:0.9em; color:#555; margin-bottom:8px; }
    .card-content a { color:#1a73e8; font-size:0.85em; word-break:break-all; }
    .card-content small { display:block; margin-top:10px; font-size:0.75em; color:#999; }

    .card-actions {
      position:absolute; bottom:12px; right:12px;
    }
    .card-actions button {
      background:#fff; border:1px solid #ddd; border-radius:4px;
      padding:6px 10px; font-size:0.8em; cursor:pointer; margin-left:6px;
      transition:background .2s, border-color .2s;
    }
    .card-actions button:hover {
      background:#f5f5f5; border-color:#ccc;
    }
  </style>
</head>
<body>
  <div class="container">

    <!-- Formular adăugare pending -->
    <div class="admin-form">
      <h2>Добавить новую ссылку</h2>
      <form id="linkForm">
      <input type="hidden" name="source" value="admin">
        <div class="form-group">
          <label>Название:</label>
          <input type="text" name="title" required>
        </div>
        <div class="form-group">
          <label>URL:</label>
          <input type="url" name="url" id="urlInput" maxlength="1000" required>
        </div>
        <div id="preview" style="text-align:center; margin-bottom:15px;"></div>
        <div class="form-group">
          <label>Описание:</label>
          <textarea name="description" rows="3"></textarea>
        </div>
        <button type="submit">Добавить</button>
        <div id="message"></div>
      </form>
    </div>

    <!-- Pending pentru moderare -->
    <h2>Pending pentru moderare</h2>
    <div class="grid">
      <?php
      $stmt = $pdo->query("SELECT * FROM pending_links ORDER BY created_at DESC");
      while($pend = $stmt->fetch()):
        $url     = htmlspecialchars($pend['url']);
        // Thumbnail YouTube
        $ytThumb = '';
        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^\&\?\/]+)/', $url, $m)) {
          $ytThumb = "https://img.youtube.com/vi/{$m[1]}/mqdefault.jpg";
        }
      ?>
      <div class="card" data-pid="<?= $pend['id'] ?>">
        <?php if ($ytThumb): ?>
          <img src="<?= $ytThumb ?>" alt="Thumbnail" style="width:100%; border-radius:8px 8px 0 0;">
        <?php endif; ?>

        <div class="card-content">
          <strong><?= htmlspecialchars($pend['title']) ?></strong>
          <?php if ($pend['description']): ?>
            <p><?= htmlspecialchars($pend['description']) ?></p>
          <?php endif; ?>
          <a href="<?= $url ?>" target="_blank"><?= $url ?></a>
          <small><?= $pend['created_at'] ?></small>
        </div>

        <div class="card-actions" style="bottom:12px; right:12px;">
          <button class="approve-btn">Approve</button>
          <button class="reject-btn">Reject</button>
        </div>
      </div>
      <?php endwhile; ?>
    </div>

    <hr style="margin:40px 0; border:none; border-top:1px solid #ddd;">

    <!-- Approved Links -->
    <h2>Approved Links</h2>
    <div class="grid">
      <?php
      $stmt2 = $pdo->query("SELECT * FROM links ORDER BY position ASC, created_at DESC");
      while($row = $stmt2->fetch()):
        $url2       = htmlspecialchars($row['url']);
        $title2     = htmlspecialchars($row['title']);
        $desc2      = htmlspecialchars($row['description']);
        $date2      = $row['created_at'];
        $ytThumb2   = '';
        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^\&\?\/]+)/', $url2, $m2)) {
          $ytThumb2 = "https://img.youtube.com/vi/{$m2[1]}/mqdefault.jpg";
        }
      ?>
      <div class="card">
        <?php if ($ytThumb2): ?>
          <img src="<?= $ytThumb2 ?>" alt="Thumbnail" style="width:100%; border-radius:8px 8px 0 0;">
        <?php endif; ?>

        <div class="card-content">
          <strong><?= $title2 ?></strong>
          <?php if ($desc2): ?>
            <p><?= $desc2 ?></p>
          <?php endif; ?>
          <a href="<?= $url2 ?>" target="_blank"><?= $url2 ?></a>
          <small><?= $date2 ?></small>
        </div>
      </div>
      <?php endwhile; ?>
    </div>

  </div>

  <script>
    // Preview thumbnail YouTube în formular
    document.getElementById('urlInput').addEventListener('input', function(){
      const m = this.value.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&?\/]+)/);
      document.getElementById('preview').innerHTML = m
        ? `<img src="https://img.youtube.com/vi/${m[1]}/maxresdefault.jpg" style="max-width:100%; border-radius:4px;">`
        : '';
    });

    // Trimite pending
    document.getElementById('linkForm').addEventListener('submit', function(e){
      e.preventDefault();
      const msg = document.getElementById('message');
      fetch('add_pending.php', { method:'POST', body:new FormData(this) })
        .then(r=>r.json()).then(d=>{
          msg.style.display = 'block';
          if(d.success){
            msg.style.background = '#d4edda';
            msg.style.color = '#155724';
            msg.textContent = 'Ссылка отправлена на модерацию!';
            this.reset();
            document.getElementById('preview').innerHTML = '';
          } else {
            msg.style.background = '#f8d7da';
            msg.style.color = '#721c24';
            msg.textContent = 'Ошибка: '+d.error;
          }
        });
    });

    // Approve / Reject
    document.querySelectorAll('.approve-btn').forEach(btn=>{
      btn.onclick = ()=>{
        const card = btn.closest('.card'), id = card.dataset.pid;
        fetch('approve.php', {
          method:'POST',
          headers:{'Content-Type':'application/json'},
          body: JSON.stringify({id})
        }).then(r=>r.json()).then(d=>{
          if(d.success) card.remove();
          else alert('Eroare la approve');
        });
      };
    });
    document.querySelectorAll('.reject-btn').forEach(btn=>{
      btn.onclick = ()=>{
        const card = btn.closest('.card'), id = card.dataset.pid;
        fetch('reject.php', {
          method:'POST',
          headers:{'Content-Type':'application/json'},
          body: JSON.stringify({id})
        }).then(r=>r.json()).then(d=>{
          if(d.success) card.remove();
          else alert('Eroare la reject');
        });
      };
    });
  </script>
</body>
</html>
