<?php get_header(); ?>

<section id="primary" class="content-area">

    <?php while(have_posts()) { the_post();

        // 获取内容格式 content.php
        get_template_part('content', get_post_format());

        // 如果评论开放，加入评论部分
        if (comments_open() || get_comments_number()) {
            comments_template();
        }

        // 上一篇/下一篇文章的链接
        the_post_navigation(array(
            'next_text' => '上一篇：%title',
            'prev_text' => '下一篇：%title',
        ));

    } ?>

</section><!-- .content-area -->

<?php get_footer();
