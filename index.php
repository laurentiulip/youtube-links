<?php
session_start();
include 'config.php';

// ———————————— LOGIN ————————————
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'login') {
  $email = trim($_POST['email']);
  $pass  = $_POST['password'];

  $stmt = $pdo->prepare("SELECT id, password_hash FROM users WHERE email = ?");
  $stmt->execute([$email]);
  $user = $stmt->fetch();

  if ($user && password_verify($pass, $user['password_hash'])) {
      $_SESSION['user_id'] = $user['id'];
  } else {
      $login_error = 'Email sau parolă incorecte';
  }
}

// —————————— REGISTER ——————————
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'register') {
  $email = trim($_POST['reg_email']);
  $p1    = $_POST['reg_password'];
  $p2    = $_POST['reg_password2'];

  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $reg_error = 'Email invalid';
  } elseif ($p1 !== $p2) {
      $reg_error = 'Parolele nu coincid';
  } else {
      $hash = password_hash($p1, PASSWORD_DEFAULT);
      try {
          $stmt = $pdo->prepare("INSERT INTO users (email, password_hash) VALUES (?, ?)");
          $stmt->execute([$email, $hash]);
          $_SESSION['user_id'] = $pdo->lastInsertId();
      } catch (PDOException $e) {
          // Cod 23000 → încălcare constrângere, de obicei cheie unică duplicat
          if ($e->getCode() === '23000') {
              $reg_error = 'Acest email este deja înregistrat';
          } else {
              $reg_error = 'Eroare la înregistrare: ' . $e->getMessage();
          }
      }
  }
}

// ———————— LOGOUT —————————
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location:index.php');
    exit;
}

$logged_in = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Полезные ссылки</title>
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:Arial,sans-serif;background:#f0f2f5;color:#333}
    .top-bar{background:#fff;padding:10px;text-align:right;box-shadow:0 2px 4px rgba(0,0,0,0.1)}
    .btn{margin-left:8px;padding:6px 12px;background:#1a73e8;color:#fff;border:none;border-radius:4px;cursor:pointer}
    .container{max-width:1200px;margin:20px auto;padding:0 20px}
    .grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:20px}
    .card{position:relative;background:#fff;border-radius:8px;overflow:hidden;
          box-shadow:0 2px 8px rgba(0,0,0,0.1);transition:transform .2s}
    .card:hover{transform:translateY(-4px)}
    .card img{width:100%;display:block}
    .card-content{padding:16px}
    .card-content a{color:#1a73e8;font-size:1.1em;text-decoration:none;display:block;margin-bottom:8px}
    .card-content p{color:#555;font-size:.9em;margin-bottom:8px}
    .card-content small{color:#999;font-size:.75em}
    .menu-btn{position:absolute;top:10px;right:10px;border:none;background:none;
              font-size:24px;color:#666;cursor:pointer}
    .menu-options{display:none;position:absolute;top:32px;right:10px;background:#fff;
                  box-shadow:0 2px 8px rgba(0,0,0,0.2);border-radius:6px;overflow:hidden;z-index:10}
    .menu-options button{display:block;padding:8px 12px;border:none;background:none;
                         text-align:left;width:100%;cursor:pointer}
    .menu-options button:hover{background:#f0f0f0}
    /* modal */
    .modal{display:none;position:fixed;top:0;left:0;right:0;bottom:0;
           background:rgba(0,0,0,0.5);justify-content:center;align-items:center;z-index:100}
    .modal-content{background:#fff;padding:20px;border-radius:8px;width:90%;max-width:360px;position:relative}
    .modal-content .close{position:absolute;top:10px;right:10px;cursor:pointer;font-size:18px}
    .form-group{margin-bottom:12px}
    .form-group label{display:block;margin-bottom:4px}
    .form-group input, .form-group textarea{width:100%;padding:8px;border:1px solid #ccc;border-radius:4px}
    .error{color:#c00;margin-bottom:12px}
  </style>
</head>
<body>

  <div class="top-bar">
    <?php if($logged_in): ?>
      <a href="?logout=1" class="btn">Logout</a>
    <?php else: ?>
      <button class="btn" id="loginBtn">Login</button>
      <button class="btn" id="regBtn">Register</button>
    <?php endif; ?>
  </div>

  <!-- LOGIN MODAL -->
  <div class="modal" id="loginModal">
    <div class="modal-content">
      <span class="close" data-close="loginModal">&times;</span>
      <h3>Login</h3>
      <?php if(!empty($login_error)): ?><div class="error"><?=$login_error?></div><?php endif; ?>
      <form method="post">
        <input type="hidden" name="action" value="login">
        <div class="form-group">
          <label>Email</label>
          <input type="email" name="email" required>
        </div>
        <div class="form-group">
          <label>Parolă</label>
          <input type="password" name="password" required>
        </div>
        <button class="btn" type="submit">Login</button>
      </form>
    </div>
  </div>

  <!-- REGISTER MODAL -->
  <div class="modal" id="regModal">
    <div class="modal-content">
      <span class="close" data-close="regModal">&times;</span>
      <h3>Register</h3>
      <?php if(!empty($reg_error)): ?><div class="error"><?=$reg_error?></div><?php endif; ?>
      <form method="post">
        <input type="hidden" name="action" value="register">
        <div class="form-group">
          <label>Email</label>
          <input type="email" name="reg_email" required>
        </div>
        <div class="form-group">
          <label>Parolă</label>
          <input type="password" name="reg_password" required>
        </div>
        <div class="form-group">
          <label>Confirmă parola</label>
          <input type="password" name="reg_password2" required>
        </div>
        <button class="btn" type="submit">Register</button>
      </form>
    </div>
  </div>

  <div class="container">
    <?php if(!$logged_in): ?>
      <p>Te rugăm să te autentifici pentru a vedea și gestiona link-urile.</p>
    <?php endif; ?>

    <div class="grid">
      <?php
      $stmt = $pdo->query("SELECT * FROM links ORDER BY position ASC, created_at DESC");
      while($row=$stmt->fetch()):
        $url=htmlspecialchars($row['url']);
        $title=htmlspecialchars($row['title']);
        $desc=htmlspecialchars($row['description']);
        $dt=$row['created_at'];
        $yt='';
        if(preg_match('/youtu(?:be\.com\/watch\?v=|\.be\/)([^\&\?\/]+)/',$url,$m)){
          $yt="https://img.youtube.com/vi/{$m[1]}/mqdefault.jpg";
        }
      ?>
      <div class="card" data-id="<?=$row['id']?>">
        <?php if($yt): ?><img src="<?=$yt?>" alt="thumb"><?php endif;?>
        <div class="card-content">
          <a href="<?=$url?>" target="_blank"><?=$title?></a>
          <?php if($desc): ?><p><?=$desc?></p><?php endif;?>
          <small><?=$dt?></small>
        </div>
        <?php if($logged_in):?>
          <button class="menu-btn">⋯</button>
          <div class="menu-options">
            <button class="delete">Șterge</button>
            <button class="edit">Editează</button>
          </div>
        <?php endif;?>
      </div>
      <?php endwhile;?>
    </div>
  </div>

  <!-- EDIT & DELETE MODALS (similar pattern) -->
  <div class="modal" id="deleteModal">
  <div class="modal-content">
    <h3>Confirmare ștergere</h3>
    <p>Ești sigur că vrei să ștergi acest link?</p>
    <button class="btn" id="confirmDelete">Da, șterge</button>
    <button class="btn" id="cancelDelete">Anulează</button>
  </div>
</div>

  <!-- EDIT MODAL -->
  <div class="modal" id="editModal">
  <div class="modal-content">
    <span class="close" id="cancelEdit">&times;</span>
    <h3>Editează link</h3>
    <form id="editForm">
      <input type="hidden" name="id" id="editId">
      <div class="form-group">
        <label>Titlu</label>
        <input type="text" name="title" id="editTitle" required>
      </div>
      <div class="form-group">
        <label>URL</label>
        <input type="url" name="url" id="editUrl" required>
      </div>
      <div class="form-group">
        <label>Descriere</label>
        <textarea name="description" id="editDescription"></textarea>
      </div>
      <button class="btn" type="submit">Salvează</button>
    </form>
  </div>
</div>

  <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
  <script>
    // Modale login/register
    ['login','reg'].forEach(id=>{
      document.getElementById(id+'Btn')?.addEventListener('click',()=>{
        document.getElementById(id+'Modal').style.display='flex';
      });
      document.querySelector('[data-close="'+id+'Modal"]')
        .addEventListener('click',()=>document.getElementById(id+'Modal').style.display='none');
    });
    window.onclick=e=>{
      if(e.target.classList.contains('modal')) e.target.style.display='none';
    };

    // Drag & drop
    Sortable.create(document.querySelector('.grid'),{
      animation:150,ghostClass:'sortable-ghost',
      onEnd:()=>{
        const order=[...document.querySelectorAll('.card')]
          .map((c,i)=>({id:c.dataset.id,position:i}));
        fetch('update_order.php',{
          method:'POST',headers:{'Content-Type':'application/json'},
          body:JSON.stringify(order)
        });
      }
    });

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
        const p = card.querySelector('p');
        document.getElementById('editDescription').value = p ? p.innerText : '';
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
  </script>
</body>
</html>
