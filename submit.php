<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Предложи ссылку</title>
  <style>
    body { font-family: sans-serif; padding: 20px; }
    .form-group { margin-bottom: 15px; }
    label { display:block; margin-bottom: 5px; }
    input, textarea, button {
      width: 100%; padding:8px; border:1px solid #ddd; border-radius:4px;
    }
    button { background: #1a73e8; color:#fff; cursor:pointer; margin-top:10px; }
    #msg { margin-top:15px; }
  </style>
</head>
<body>
  <h2>Предложи ссылку для сайта</h2>
  <form id="submitForm">
  <input type="hidden" name="source" value="user">
    <div class="form-group">
      <label>Название:</label>
      <input type="text" name="title" required>
    </div>
    <div class="form-group">
      <label>URL:</label>
      <input type="url" name="url" required>
    </div>
    <div class="form-group">
      <label>Описание (опционально):</label>
      <textarea name="description"></textarea>
    </div>
    <button type="submit">Отправить на модерацию</button>
  </form>
  <div id="msg"></div>

  <script>
    document.getElementById('submitForm').addEventListener('submit', function(e){
      e.preventDefault();
      const form = new FormData(this);
      fetch('add_pending.php', { method:'POST', body: form })
        .then(r=>r.json()).then(data=>{
          const msg = document.getElementById('msg');
          if(data.success){
            msg.style.color = 'green';
            msg.innerText = 'Спасибо! Ссылка отправлена на модерацию.';
            this.reset();
          } else {
            msg.style.color = 'red';
            msg.innerText = 'Ошибка: ' + data.error;
          }
        });
    });
  </script>
</body>
</html>
