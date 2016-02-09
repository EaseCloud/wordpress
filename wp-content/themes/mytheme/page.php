<?php get_header(); ?>

<!-- 标准 Sidebar Markup -->
<div id="sidebar" class="sidebar">
    <?php get_sidebar(); ?>
</div><!-- .sidebar -->

<!-- 标准 正文内容 Markup -->
<div id="primary" class="content-area">

    <?php while (have_posts()) {

        the_post();

        // 获取内容格式 content.php
        get_template_part('content', 'page');

        // 如果评论开放，加入评论部分
        if (comments_open() || get_comments_number()) {
            comments_template();
        }

    }
    ?>

</div><!-- .content-area -->

<?php get_footer(); ?>
