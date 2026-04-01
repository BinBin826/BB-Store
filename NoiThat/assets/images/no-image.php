<?php
// Tạo ảnh placeholder SVG
header('Content-Type: image/svg+xml');
echo '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="400" viewBox="0 0 400 400">
  <rect width="400" height="400" fill="#F0EDE8"/>
  <text x="200" y="190" font-family="serif" font-size="80" text-anchor="middle" fill="#C8C0B5">🛋️</text>
  <text x="200" y="250" font-family="sans-serif" font-size="16" text-anchor="middle" fill="#C8C0B5">Chưa có hình ảnh</text>
</svg>';
