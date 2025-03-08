<?php
class GitHubGrass
{
    private string $token;
    private string $username;
    private array $weeks = [];
    private int $cellSize = 12;
    private int $padding = 2;
    private array $colors = [];

    public function __construct(string $username, string $token)
    {
        $this->username = $username;
        $this->token = $token;

        // 色の設定（GitHub風）
        $this->colors = [
            'level0' => [235, 237, 240],
            'level1' => [155, 233, 168],
            'level2' => [64, 196, 99],
            'level3' => [48, 161, 78],
            'level4' => [33, 110, 57]
        ];
    }

    /**
     * GitHub APIから草データを取得
     */
    public function fetchContributions(): bool
    {
        $url = 'https://api.github.com/graphql';
        $query = <<<'JSON'
        {
          user(login: "USERNAME") {
            contributionsCollection {
              contributionCalendar {
                weeks {
                  contributionDays {
                    contributionCount
                  }
                }
              }
            }
          }
        }
        JSON;

        // ユーザー名を埋め込む
        $query = str_replace("USERNAME", $this->username, $query);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->token,
            'User-Agent: ContributionsApp'
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['query' => $query]));

        $response = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($response, true);
        if (!isset($data['data']['user']['contributionsCollection']['contributionCalendar']['weeks'])) {
            $this->weeks = []; // 空の配列を設定
            return false;
        }

        $this->weeks = $data['data']['user']['contributionsCollection']['contributionCalendar']['weeks'];
        return true;
    }

    /**
     * 草画像を生成
     */
    public function generateImage(): string
    {
        if (count($this->weeks)) {
            die('No data available.');
        }

        // 画像サイズ設定
        $width = (count($this->weeks) * ($this->cellSize + $this->padding)) + $this->padding;
        $height = (7 * ($this->cellSize + $this->padding)) + $this->padding;

        // 画像作成
        $image = imagecreatetruecolor($width, $height);
        $bgColor = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $bgColor);

        // 色の作成
        $colorPalette = [];
        foreach ($this->colors as $key => $rgb) {
            $colorPalette[$key] = imagecolorallocate($image, ...$rgb);
        }

        // セル描画
        foreach ($this->weeks as $x => $week) {
            foreach ($week['contributionDays'] as $y => $day) {
                $count = $day['contributionCount'];
                $color = $this->getColor($count, $colorPalette);

                // 四角形を描画
                imagefilledrectangle(
                    $image,
                    $x * ($this->cellSize + $this->padding) + $this->padding,
                    $y * ($this->cellSize + $this->padding) + $this->padding,
                    ($x + 1) * ($this->cellSize + $this->padding),
                    ($y + 1) * ($this->cellSize + $this->padding),
                    $color
                );
            }
        }

        // 出力
        ob_start();
        imagepng($image);
        $imageData = ob_get_contents();
        ob_end_clean();
        imagedestroy($image);

        $base64 = base64_encode($imageData);

        return $base64;
    }

    /**
     * コントリビューション数に応じた色を取得
     */
    private function getColor(int $count, array $colorPalette)
    {
        if ($count == 0) {
            return $colorPalette['level0'];
        } elseif ($count < 5) {
            return $colorPalette['level1'];
        } elseif ($count < 10) {
            return $colorPalette['level2'];
        } elseif ($count < 20) {
            return $colorPalette['level3'];
        } else {
            return $colorPalette['level4'];
        }
    }
}
