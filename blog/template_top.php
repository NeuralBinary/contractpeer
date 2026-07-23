<?php
// Blog article template — all blog articles use this
$page_title = isset($page_title) ? $page_title : 'Blog';
$page_description = isset($page_description) ? $page_description : '';
$extra_head = '<style>
.article{max-width:760px;margin:0 auto;padding:60px 0;line-height:1.75;}
.article h1{font-size:2rem;margin-bottom:8px;}
.article .meta{color:#9ca3af;font-size:0.9rem;margin-bottom:32px;}
.article h2{font-size:1.5rem;margin-top:36px;margin-bottom:12px;}
.article h3{font-size:1.15rem;margin-top:24px;margin-bottom:8px;}
.article p{margin-bottom:16px;color:#374151;}
.article ul{margin-bottom:16px;padding-left:24px;}
.article li{margin-bottom:8px;color:#374151;}
.cta-box{background:#dbeafe;border-radius:12px;padding:24px;margin:32px 0;text-align:center;}
.article img{max-width:100%;height:auto;border-radius:12px;margin:24px 0;}
</style>';
require __DIR__ . '/../templates/header.php';
?>
<div class="container article">
