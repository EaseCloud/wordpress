<?php

/**
 * 动态版本码以消灭静态文件缓存引起的问题
 */
define('VERSION', '1.0.0');
define('DYNAMIC_VERSION', WP_DEBUG ? strval(rand()) : VERSION);
define('TDM', 'THEME_DOMAIN');

/**
 * 导入自定义 PostType 类
 */
if(file_exists(__DIR__.'/class/CustomPost.class.php')) {
    require __DIR__.'/class/CustomPost.class.php';
}

/**
 * 指定内容宽度
 */
if(!isset($content_width)) {
    $content_width = 600;
}

/**
 * 主题加载的动作
 */
add_action('after_setup_theme', function() {

    // 1. 支持特色图像
    add_theme_support( 'post-thumbnails' );
    // set_post_thumbnail_size( 672, 372, true );
    // add_image_size( 'image-full-width', 1038, 576, true );

    // 2. 注册菜单栏
    register_nav_menus( array(
        'primary'   => __('Main Menu', TDM),
        'secondary' => __('Sidebar Menu', TDM),
        'footer' => __('Footer Menu', TDM),
        'mobile' => __('Mobile Menu', TDM),
    ) );

    // 3. 使用默认的组件 markup
    add_theme_support('html5', array(
        'search-form', 'comment-form', 'comment-list', 'gallery', 'caption'
    ));

    // 4. 支持的文章格式，参考：http://codex.wordpress.org/Post_Formats
    add_theme_support( 'post-formats', array(
        'aside', 'image', 'video', 'audio', 'quote', 'link', 'gallery',
    ));

    // 4.1. 让页面类型支持摘要
    add_post_type_support('page', array('excerpt'));

    // 5. 支持自动 feed
    add_theme_support('automatic-feed-links');

    // 6. 自动添加 <head> 里面的 title 属性
    add_theme_support('title-tag');

    // 7. 自定义顶部（顶部图像 - 可用作 LOGO，默认大小按照百度缩略图尺寸优化）
    // src: header_image(); height: get_custom_header()->height; width: get_custom_header()->width;
    add_theme_support('custom-header', array(
        'width'         => 121,
        'height'        => 75,
        'default-image' => get_template_directory_uri() . '/images/header.jpg',
    ));

    // 8. 支持自定义背景图图像
    add_theme_support('custom-background');

    // 9. 只要是非手机端登录的用户就显示顶部管理菜单栏
    add_filter( 'show_admin_bar', function() {
        return is_user_logged_in() && !wp_is_mobile(); // 手机端不显示 admin_bar
    });

    // 10. 修改标题分隔符，默认为 '-'
    add_filter('document_title_separator', function() { return ' | '; });

    // 11. 图片默认无链接
    update_option('image_default_link_type', 'none');

});

/**
 * 模板引入的 js 和 css 文件库
 */
add_action( 'wp_enqueue_scripts', function() {

    // 0. WordPress 内置脚本
    // 0.1. 评论回复脚本
    if (is_singular()) wp_enqueue_script( "comment-reply" );

    // 1. CSS 样式重置
    $path = '/lib/HTML5-Reset/assets/css/reset.css';
    if(file_exists(__DIR__.$path))
        wp_enqueue_style('reset', get_template_directory_uri().$path,
            array(), '2.1.5');

    // 2. 轮播组件 owl-carousel （任选其一）
    // 2.1. Owl Carousel 2
    // http://www.owlcarousel.owlgraphic.com/
    $path = '/lib/OwlCarousel2/dist/assets/owl.carousel.min.css';
    if(file_exists(__DIR__.$path))
        wp_enqueue_style('owl-2', get_template_directory_uri().$path,
            array(), '2.1.3');
    $path = '/lib/OwlCarousel2/dist/assets/owl.theme.default.min.css';
    if(file_exists(__DIR__.$path))
        wp_enqueue_style('owl-2-theme', get_template_directory_uri().$path,
            array(), '2.1.3');
    $path = '/lib/OwlCarousel2/dist/owl.carousel.min.js';
    if(file_exists(__DIR__.$path))
        wp_enqueue_script('owl-2', get_template_directory_uri().$path,
            array('jquery'), '2.1.3', true);

//    // 2.2. Owl Carousel 1
//    // http://www.owlgraphic.com/owlcarousel/
//    $path = '/lib/OwlCarousel/owl-carousel/owl.carousel.css';
//    if(file_exists(__DIR__.$path))
//        wp_enqueue_style('owl', get_template_directory_uri().$path,
//            array(), '1.3.3');
//    $path = '/lib/OwlCarousel/owl-carousel/owl.theme.css';
//    if(file_exists(__DIR__.$path))
//        wp_enqueue_style('owl-theme', get_template_directory_uri().$path,
//            array(), '1.3.3');
//    $path = '/lib/OwlCarousel/owl-carousel/owl.transitions.css';
//    if(file_exists(__DIR__.$path))
//        wp_enqueue_style('owl-transitions', get_template_directory_uri().$path,
//            array(), '1.3.3');
//    $path = '/lib/OwlCarousel/owl-carousel/owl.carousel.min.js';
//    if(file_exists(__DIR__.$path))
//        wp_enqueue_script('owl', get_template_directory_uri().$path,
//            array('jquery'), '1.3.3', true);

//    // 3. jquery.form.js(Introduced in WordPress Core)
//    // http://plugins.jquery.com/form/
//    // http://malsup.com/jquery/form/
//    $path = '/lib/form/jquery.form.js';
//    if(file_exists(__DIR__.$path))
//        wp_enqueue_script('jquery-form', get_template_directory_uri().$path,
//            array('jquery'), '3.51', true);

    // 4. Dashicons 图标库
    // https://developer.wordpress.org/resource/dashicons/
    wp_enqueue_style('dashicons',
        includes_url('/css/dashicons.min.css'), array(), get_bloginfo('version'));

    // 5. Font Aowesome 图标库
    // http://fontawesome.io/
    $path = '/lib/Font-Awesome/css/font-awesome.min.css';
    if(file_exists(__DIR__.$path))
        wp_enqueue_style('font-awesome', get_template_directory_uri().$path,
            array(), '4.5.0');

    // 6. jpeg 压缩器
    // http://web.archive.org/web/20120830003356/http://www.bytestrom.eu/blog/2009/1120a_jpeg_encoder_for_javascript
    $path = '/lib/jpeg_encoder/jpeg_encoder_basic.js';
    if(file_exists(__DIR__.$path))
        wp_enqueue_script('jpeg-encoder', get_template_directory_uri().$path,
            array(), '0.9a', true);

    // 7. hammer.js 触屏插件
    // https://github.com/hammerjs/hammer.js
    $path = '/lib/hammer.js/hammer.min.js';
    if(file_exists(__DIR__.$path))
        wp_enqueue_script('hammer', get_template_directory_uri().$path,
            array('jquery'), '2.0.6', true);

    // 8. WOW 飞入效果插件
    $path = '/lib/WOW/dist/wow.min.js';
    if(file_exists(__DIR__.$path))
        wp_enqueue_script('wow', get_template_directory_uri().$path,
            array('jquery'), '1.1.2', true);

    // 9. animate.css 动画
    $path = '/lib/animate.css/animate.min.css';
    if(file_exists(__DIR__.$path))
        wp_enqueue_style('animate', get_template_directory_uri().$path,
            array(), '3.5.0');

    // 10. jquery-ui
    wp_enqueue_script(
        'jquery-ui',
        get_template_directory_uri().'/lib/jquery-ui/jquery-ui.min.js',
        array( 'jquery' ), '1.11.4'
    );
    wp_enqueue_script(
        'jquery-ui-touch-punch',
        get_template_directory_uri().'/lib/jquery-ui/jquery.ui.touch-punch.min.js',
        array( 'jquery-ui' ), '0.2.3'
    );
    wp_enqueue_style(
        'jquery-ui',
        get_template_directory_uri().'/lib/jquery-ui/jquery-ui.min.css',
        array(),
        '1.11.4'
    );
    wp_enqueue_style(
        'jquery-ui-structure',
        get_template_directory_uri().'/lib/jquery-ui/jquery-ui.structure.min.css',
        array(),
        '1.11.4'
    );

    // 10. 工具库
//    wp_enqueue_script( 'easecloud', 'http://lib.easecloud.cn/wp-content/themes/easecloud/js/functions.js', array( 'jquery-ui' ), '1.0');

    // 99. 模板自定义的 CSS/JS
    wp_enqueue_style(
        'template-style',
        get_template_directory_uri().'/style.css',
        array('reset'),
        DYNAMIC_VERSION
    );
    wp_enqueue_script(
        'template-script',
        get_template_directory_uri().'/js/functions.js',
        array('jquery'),
        DYNAMIC_VERSION
    );

    // 99.1. 移除默认的 Google 字体
    wp_deregister_style( 'open-sans' );
    wp_register_style( 'open-sans', false );
    wp_enqueue_style('open-sans','');

});


/**
 * 注册小工具区
 */
add_action('widgets_init', function() {
    register_sidebar( array(
        'name'          => '侧栏挂件区',
        'id'            => 'widget-sidebar',
        'description'   => '从这里将你的小工具添加到页面侧栏',
        'before_widget' => '<aside id="%1$s" class="widget %2$s">',
        'after_widget'  => '</aside>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ) );
});


/**
 * 添加后台样式
 */
add_action('admin_enqueue_scripts', function() {

    // 1. 添加自定义样式和脚本
    wp_enqueue_style(
        'admin-style',
        get_template_directory_uri().'/style-admin.css',
        false,
        DYNAMIC_VERSION
    );
    wp_enqueue_script(
        'admin-script',
        get_template_directory_uri().'/js/functions-admin.js',
        array('jquery'),
        DYNAMIC_VERSION
    );

    // 2. 移除后台默认的 Google 字体
    wp_deregister_style( 'open-sans' );
    wp_register_style( 'open-sans', false );
    wp_enqueue_style('open-sans','');

});


/**
 * 为 body class 添加 page-[slug], single-[class] 等等 html class
 * @param $classes: 之前处理生成的 class 列表
 * @return array: 处理之后生成的 class 列表
 */
add_filter('body_class', function($classes) {
    // 页面的处理
    if(is_page()) {
        global $post;
        $classes []= 'page-'.$post->post_name;
    }
    // 文章的处理
    if(is_single()) {
        global $post;
        $classes []= 'single-'.$post->post_name;
    }
    // 加入是 pc 或者 mobile 的类
    $classes []= wp_is_mobile() ? 'ua-mobile' : 'ua-pc';
    // 返回结果
    return $classes;
}, 10, 1);


/**
 * 编辑器管理
 */
add_action('admin_init', function() {

    // 添加编辑器样式表
    add_editor_style(get_template_directory_uri().'/css/editor-style.css');

    // 为编辑器添加调整字号的按钮
    add_filter('mce_buttons_3', function($buttons) {
        $buttons[] = 'hr'; $buttons[] = 'fontselect';
        $buttons[] = 'fontsizeselect';
        return $buttons;
    });
});


/**
 * TODO: 此部分功能后面计划移出到独立的插件
 * 插入【置顶】、【隐藏】的评论操作标签
 */
function comment_row_action( $actions, $comment ) {
    // 置顶/取消置顶
    if(get_comment_meta($comment->comment_ID, 'is_sticky', true) === '1') {
        $new_actions['stick'] = '<a class="comment-action-custom"
            href="javascript:;" data-action="unstick" data-post-id="'.$comment->comment_post_ID.'"
            data-comment-id="'.$comment->comment_ID.'">取消置顶</a>';
    } else {
        $new_actions['unstick'] = '<a class="comment-action-custom"
            href="javascript:;" data-action="stick" data-post-id="'.$comment->comment_post_ID.'"
            data-comment-id="'.$comment->comment_ID.'">置顶</a>';
    }
    // 隐藏内容/显示内容
    if(get_comment_meta($comment->comment_ID, 'is_hide', true) === '1') {
        $new_actions['show'] = '<a class="comment-action-custom"
            href="javascript:;" data-action="show" data-post-id="'.$comment->comment_post_ID.'"
            data-comment-id="'.$comment->comment_ID.'">显示内容</a>';
    }
    else{
        $new_actions['hide'] = '<a class="comment-action-custom"
            href="javascript:;" data-action="hide" data-post-id="'.$comment->comment_post_ID.'"
            data-comment-id="'.$comment->comment_ID.'">隐藏内容</a>';
    }

    // 在前边插入新增的两个动作链接并返回
    return array_merge($new_actions, $actions);
}
add_filter('comment_row_actions', 'comment_row_action', 10, 2);


/**
 * TODO: 此部分功能后面计划移出到独立的插件
 * ajax 处理新增的评论操作动作【置顶】、【隐藏】
 */
add_action('wp_ajax_comment_action_custom', function() {
    if(!empty($_REQUEST['action_comment']) && !empty($_REQUEST['comment_id'])) {
        // 执行修改的动作
        $action_comment = $_REQUEST['action_comment'];
        $comment_id = intval($_REQUEST['comment_id']);
        $comment = get_comment($comment_id);
        if($action_comment == 'stick') {
            update_comment_meta($comment_id, 'is_sticky', 1);
        } elseif($action_comment == 'unstick') {
            update_comment_meta($comment_id, 'is_sticky', 0);
        } elseif($action_comment == 'hide') {
            update_comment_meta($comment_id, 'is_hide', 1);
        } elseif($action_comment == 'show') {
            update_comment_meta($comment_id, 'is_hide', 0);
        }
        // 获取修改后的 markup
        ob_start();
        $wp_list_table = _get_list_table(
            strpos($_SERVER['HTTP_REFERER'], 'wp-admin/post.php') === false ?
                'WP_Comments_List_Table' : 'WP_Post_Comments_List_Table',
            array( 'screen' => 'edit-comments' )
        );
        $wp_list_table->single_row( $comment );
        $comment_list_item = ob_get_clean();
        // 打包输出
        $x = new WP_Ajax_Response();
        $x->add( array(
            'what' => 'edit_comment',
            'id' => $comment->comment_ID,
            'data' => $comment_list_item,
            'position' => -1
        ));
        $x->send();
        exit(0);
    }
});


/**
 * TODO: 此部分功能后面计划移出到独立的插件
 * 客户端点击评论操作时的动作
 */
add_action('admin_footer', function () {?>
    <script>
        jQuery(function($) {
            $('body').on('click', '.comment-action-custom', function() {
                var comment_id = parseInt($(this).data('comment-id'));
                var comment_post_id = parseInt($(this).data('comment-post-id'));
                $.post(ajaxurl, {
                    action: 'comment_action_custom',
                    action_comment: $(this).data('action'),
                    comment_id: comment_id,
                    comment_post_id: comment_post_id
                }, function(xml) {
                    var $old = $('#comment-' + comment_id);
                    var $new = $.trim(wpAjax.parseAjaxResponse(xml).responses[0].data);
                    $old.after($new).remove();
                });
            });
        });
    </script><?php
});


/**
 * TODO: 此部分功能后面计划移出到独立的插件
 * 修改后台显示的评论作者状态，加上【置顶】
 */
function add_admin_comment_author_state($author, $comment_ID, $comment) {
    if(is_admin()) {
        if(get_comment_meta($comment->comment_ID, 'is_sticky', true) === '1') {
            $author = '[顶]'.$author;
        }
    }
    return $author;
}
add_filter('get_comment_author', 'add_admin_comment_author_state', 10, 3);


/**
 * 移动端跳转
 * 如果当前 wp_is_mobile 为真
 * 而且路径渲染的模板文件存在 [mobile] 版本
 * 则使用 [mobile] 的模板文件）
 */
add_filter('template_include', function($template) {

    $mobile_template_file = preg_replace('/\\.php$/', '[mobile].php', $template);

    if(wp_is_mobile() && file_exists($mobile_template_file)) {
        return $mobile_template_file;
    }

    return $template;

}, 10, 1);


/**
 * 添加所有 post 类型的置顶功能
 */
add_filter('post_row_actions', function($actions, $post) {

    $new_actions = array();

    // 置顶/取消置顶
    if(get_post_meta($post->ID, 'is_sticky', true) === '1') {
        $new_actions['stick'] = '<a class="post-action-custom"
            href="javascript:;" data-action="unstick"
            data-post-type="'.$post->post_type.'"
            data-post-id="'.$post->ID.'">取消置顶</a>';
    } else {
        $new_actions['unstick'] = '<a class="post-action-custom"
            href="javascript:;" data-action="stick"
            data-post-id="'.$post->ID.'">置顶</a>';
    }

    // 在前边插入新增的两个动作链接并返回
    return array_merge($new_actions, $actions);

}, 10, 2);


/**
 * 客户端点击文章操作时的动作
 */
add_action('admin_footer', function () { ?>
    <script>
        jQuery(function($) {
            $('body').on('click', '.post-action-custom', function() {
                var post_id = parseInt($(this).data('post-id'));
                $.post(ajaxurl, {
                    action: 'post_action_custom',
                    action_post: $(this).data('action'),
                    post_type: $(this).data('post-type'),
                    post_ID: $(this).data('post-id')
                }, function(data) {
//                    console.log(data);
                    location.reload();
                });
            });
        });
    </script><?php
});


/**
 * ajax 处理新增的文章操作动作【置顶】
 */
add_action('wp_ajax_post_action_custom', function () {
    if(!empty($_REQUEST['action_post']) && !empty($_REQUEST['post_ID'])) {
        // 执行修改的动作
        $action_post = $_REQUEST['action_post'];
        $post_id = intval($_REQUEST['post_ID']);
        $sticky_posts = get_option('sticky_posts');
        switch($action_post) {
            case 'stick':
                update_post_meta($post_id, 'is_sticky', 1);
                $sticky_posts []= $post_id;
                break;
            case 'unstick':
                update_post_meta($post_id, 'is_sticky', 0);
                $key = array_search($post_id, $sticky_posts);
                array_splice($sticky_posts, $key,1);
                break;
        }
        update_option('sticky_posts', $sticky_posts);
        exit(0);
    }
});


/**
 * @param $id
 * 更新文章的时候根据置顶设置设定 is_sticky 值
 */
function set_stick_after_post_update($id) {
    update_post_meta($id, 'is_sticky', in_array($id, get_option( 'sticky_posts' )) ? 1 : 0);
}
add_action('post_updated', 'set_stick_after_post_update');


/**
 * @param $option
 * 更新置顶状态的时候刷新所有的 is_sticky 属性
 */
function update_sticky_posts($option, $old_val, $val) {
    if($option == 'sticky_posts') {
        global $wpdb;
        // 让所有的文章都支持 is_sticky
        $wpdb->query("
            insert into {$wpdb->postmeta} (post_id, meta_key, meta_value)
            select p.ID, 'is_sticky', 0 from {$wpdb->posts} p
            where p.post_type in ('post', 'member', 'coupon', 'goods', 'community')
                and not exists(
                    select * from {$wpdb->postmeta} pm
                    where pm.post_id = p.ID and pm.meta_key = 'is_sticky'
                );
            ");

        // 让所有文章的 is_sticky 为 0
        $wpdb->query("
            update {$wpdb->postmeta} set meta_value = 0
            where meta_key = 'is_sticky';");

        // 让置顶的文章 is_sticky 为 1
        $wpdb->query("
            update {$wpdb->postmeta} set meta_value = 1
            where post_id in (".(implode(',', get_option('sticky_posts'))).")
                and meta_key = 'is_sticky';
            ");

    }
}
add_action('updated_option', 'update_sticky_posts', 10, 3);


/**
 * 屏蔽头像
 */
add_filter('get_avatar', function () {
    return '<img src="'.get_template_directory_uri().'/images/avatar.jpg" />';
});


/**
 * @param $post_id
 * @param $size (thumbnail|small|medium|medium-large|large|full)?
 * @return array|bool|string
 * 有缩略图返回缩略图，没有的话返回默认图片
 */
function get_post_thumbnail_url($post_id, $size='large') {
    $large_image_url = wp_get_attachment_image_src(
        get_post_thumbnail_id($post_id), $size);
    return $large_image_url ? $large_image_url[0] :
        get_template_directory_uri().'/images/default_thumbnail.png';
}


/**
 * @param $output
 * @return int
 * 限定excerpt的长度
 */
add_filter('the_excerpt', function($output) {
    $limit = get_option('excerpt_trim_length', 80);
    $output = mb_strlen($output) > $limit ?
        mb_substr($output,0, $limit).get_option('excerpt_ellipsis', '[...]'):
        $output;
    return $output;
});


/**
 * 手动加入页面作为手机版的 PC 适配显示
 */
function pc_adjust() {
if(wp_is_mobile() || @$_GET['_embed'] == '1') return;
ob_clean();
$url = $_SERVER['REQUEST_URI'];
if(strpos($url, '?') !== false) $url .= '&'; else $url .= '?';
?><!DOCTYPE html><html><head><title><?=wp_get_document_title()?></title></head>
<body style="background: #555">
<iframe width="480" height="848" style="margin: 0 auto; border:0" src="<?=$url?>_embed=1"></iframe>
<script>
    if(top !== window && !/_embed=1/.test(location.query)) {
        top.location.href = location.href;
    }
</script>
</body>
</html><?php exit;
}


/**
 * 让当前进程驻留运行然后跳转到指定的页面
 * @param $redirect_url
 */
function fork_redirect($redirect_url) {
    ob_end_flush();
    ob_start();
    set_time_limit(0);
    ignore_user_abort(true);
    wp_redirect($redirect_url);
    header('Content-Length: 0');
    echo str_repeat(' ', 4096*1024);
    ob_flush();
    flush();
    sleep(5);
}


/**
 * 输出特殊类型的文章列表 markup
 */
function post_list_special($type, $count=10,
                           $with_date=false,
                           $post_type='post',
                           $with_excerpt=false) {
    global $wpdb;
    $posts = array();
    $query_arr = array();
    if($type == 'new') {
        $query_arr = array(
            'post_type' => $post_type,
            'orderby' => 'post_date',
            'order' => 'DESC',
            'posts_per_page' => $count,
        );
    } elseif($type == 'hot') {
        $query_arr = array(
            'post_type' => $post_type,
            "orderby" => 'meta_value_num',
            "meta_key" => '_post_view_count',
            "order" => 'DESC',
            'posts_per_page' => $count,
        );
    } elseif($type == 'random') {
        $query_arr = array(
            'post_type' => $post_type,
            'orderby' => 'rand',
            'posts_per_page' => $count,
        );
    }
    // 为post筛选出需要的分类的资讯
    if($post_type=='post') {
        $query_arr['category_name'] = 'all-news';
    }
    $posts = get_posts($query_arr);
    ?><ul class="post-list<?=$with_date?' with-date':''?>"><?php
    foreach($posts as $post) {
        if(!$count--) break; // 条数限制
        ?><li>
        <?php setup_postdata($post); ?>
        <?php if($with_date) {?>
            <span class="list-item-date">
                <?php echo get_the_date('Y-m-d', $post);?>
            </span>
        <?php }?>
        <?php if($with_excerpt) {?>
            <a href="<?php echo get_permalink($post); ?>">
                <img class="thumbnail" src="<?php
                $large_image_url = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'large');
                echo empty($large_image_url) ? get_template_directory_uri().'/images/logo.png' : $large_image_url[0];
                ?>">
                <p class="list-item-title" ><?php echo $post->post_title;?></p>
                <p class="list-item-excerpt"><?php
                    $excerpt_length_limit = 25;
                    $clean_content = strip_tags(preg_replace('/\[show_img\][\s\S]*\[\/show_img\]|\[\/?case_item.*?\]|\[banner_img\][\s\S]*\[\/banner_img\]|&nbsp;|\s*/', '', $post->post_content));
                    echo mb_substr($clean_content, 0, $excerpt_length_limit);
                    echo (mb_strlen($clean_content) > $excerpt_length_limit) ?  '[...]' : '';
                    ?></p>
            </a>
        <?php } else { ?>
            <a href="<?=get_permalink($post); ?>"><?php echo get_the_title($post); ?></a>
        <?php }?>
        </li><?php
        wp_reset_postdata();
    }
    ?></ul><?php
}


/**
 * 初始化预定义的页面
 */
add_action('admin_head', function() {

    $init_pages = array(
        //'page-slug' => '页面名称',
    );

    foreach($init_pages as $key => $val) {
        $posts = get_posts(array(
            'post_type' => 'page',
            'name' => $key,
            'post_status' => array('publish', 'trash', 'pending', 'draft', 'future'),
        ));
        if(sizeof($posts) == 0) {
            wp_insert_post(array(
                'post_type' => 'page',
                'post_name' => $key,
                'post_title' => $val,
                'post_status' => 'publish',
            ));
        }
    }

});

