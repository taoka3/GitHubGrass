# GitHubGrass
クラシックAPI（トークン）を使用してGitHub APIから草データを取得.  
使用方法  
```php
<?php  
        require_once 'config.php';
        require_once 'GitHubGrass.php';          
        // GitHubの設定
        $token = YOUR_GITHUB_PERSONAL_ACCESS_TOKEN;
        $username = YOUR_GITHUB_USERNAME;

        // クラスのインスタンス化
        $grass = new GitHubGrass($username, $token);

        // データ取得 & 画像生成
        if ($grass->fetchContributions()) {
            echo '<img src="data:image/png;base64,' . $grass->generateImage() . '" alt="GitHub Contribution" class="github-grass">';
        }
?>
```
![image](https://github.com/user-attachments/assets/b2c34d99-ef65-47c4-8325-b02c7ee29e2d)
