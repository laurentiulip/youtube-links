<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админ-панель</title>
    <style>
        .admin-form {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            padding: auto;
            background: #f5f5f5;
            border-radius: 8px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input, textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            background: #1a73e8;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        #message {
            margin-top: 15px;
            padding: 10px;
            display: none;
        }
        #preview {
            margin-top: 20px;
            text-align: center;
        }
        #preview img {
            max-width: 100%;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="admin-form">
        <h2>Добавить новую ссылку</h2>
        <form id="linkForm">
            <div class="form-group">
                <label>Название:</label>
                <input type="text" name="title" required>
            </div>
            <div class="form-group">
                <label>URL:</label>
                <input type="url" name="url" id="urlInput" maxlength="1000" required>
            </div>
            <div id="preview"></div> <!-- aici punem thumbnail-ul dinamic -->
            <div class="form-group">
                <label>Описание:</label>
                <textarea name="description" rows="3" ></textarea>
            </div>
            <button type="submit">Добавить</button>
        </form>
        <div id="message"></div>
    </div>

    <script>
        const urlInput = document.getElementById('urlInput');
        const preview = document.getElementById('preview');

        urlInput.addEventListener('input', function() {
            const url = this.value;
            const youtubeRegex = /(?:youtube\.com\/watch\?v=|youtu\.be\/)([^\&\?\/]+)/;
            const match = url.match(youtubeRegex);

            if (match && match[1]) {
                const videoId = match[1];
                const thumbnailUrl = `https://img.youtube.com/vi/${videoId}/maxresdefault.jpg`;
                preview.innerHTML = `<img src="${thumbnailUrl}" alt="YouTube Thumbnail">`;
            } else {
                preview.innerHTML = '';
            }
        });

        document.getElementById('linkForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const message = document.getElementById('message');
            
            fetch('add_link.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                message.style.display = 'block';
                if(data.success) {
                    message.innerHTML = 'Ссылка успешно добавлена!';
                    message.style.backgroundColor = '#d4edda';
                    message.style.color = '#155724';
                    this.reset();
                    preview.innerHTML = ''; // ștergem thumbnail-ul după adăugare
                } else {
                    message.innerHTML = 'Ошибка: ' + data.error;
                    message.style.backgroundColor = '#f8d7da';
                    message.style.color = '#721c24';
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    </script>
</body>
</html>
