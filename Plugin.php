<?php
/**
 * 文章评论推送到饭碗警告（https://fwalert.com/）
 *
 * @package Comment2Fanwan
 * @author Y!an
 * @version 1.0.0
 * @link https://yian.me
 */
class Comment2Fanwan_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     *
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
        Typecho_Plugin::factory('Widget_Feedback')->comment = array('Comment2Fanwan_Plugin', 'fw');
        Typecho_Plugin::factory('Widget_Feedback')->trackback = array('Comment2Fanwan_Plugin', 'fw');
        Typecho_Plugin::factory('Widget_XmlRpc')->pingback = array('Comment2Fanwan_Plugin', 'fw');
        
        return _t('请配置饭碗警告的 Webhook 地址, 以使您的新评论推送生效');
    }
    
    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     *
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate()
    {
    }
    
    /**
     * 获取插件配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        $key = new Typecho_Widget_Helper_Form_Element_Text('webhook', null, null, _t('Webhook URL'), _t('在 <a href="https://fwalert.com/115">饭碗警告</a> 中添加一个新的转发规则，并将出发类型设置为 <code>Webhook</code>，然后将 Webhook 地址填写到这里'));
        $form->addInput($key->addRule('required', _t('Webhook 不能为空')));
    }
    
    /**
     * 个人用户的配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {
    }

    /**
     * 微信推送
     *
     * @access public
     * @param array $comment 评论结构
     * @param Typecho_Widget $post 被评论的文章
     * @return void
     */
    public static function fw($comment, $post)
    {
        $options = Typecho_Widget::widget('Widget_Options');

        $webhook = $options->plugin('Comment2Fanwan')->webhook;

        $postdata = json_encode(
            [
                'title'      => $post->title,
                'comment'    => $comment['text'],
                'author'     => $comment['author'],
                'created_at' => date('Y-m-d H:i:s', $comment['created']),
                'url'        => $post->permalink,
            ],
            JSON_UNESCAPED_UNICODE
        );

        $opts = [
            'http' => [
                'method'  => 'POST',
                'header'  => 'Content-type: application/json',
                'content' => $postdata
            ],
        ];
        $context  = stream_context_create($opts);
        $result = file_get_contents($webhook, false, $context);
        return $comment;
    }
}
