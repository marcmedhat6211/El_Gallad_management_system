<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use PN\Bundle\ProductBundle\Entity\Product;
use PN\Bundle\SeoBundle\Entity\Seo;
use PN\SeoBundle\Entity\SeoBaseRoute;
use PN\SeoBundle\Entity\SeoPage;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210929140852 extends AbstractMigration implements ContainerAwareInterface
{

    use ContainerAwareTrait;

    public function up(Schema $schema) : void
    {
        //LANGUAGE
        $this->addSql("INSERT INTO `language` (`id`, `locale`, `title`, `flag_asset`) VALUES (1, 'ar', 'Arabic', 'admin/images/flags/eg.png')");

        //IMAGE TYPE
        $this->addSql("INSERT INTO `image_type` (`id`, `name`) VALUES (1, 'Main Image')");
        $this->addSql("INSERT INTO `image_type` (`id`, `name`) VALUES (2, 'Gallery')");
        $this->addSql("INSERT INTO `image_type` (`id`, `name`) VALUES (3, 'Cover Photo')");

        //IMAGE SETTINGS
        $this->addSql("INSERT INTO `image_setting` (`id`, `entity_name`, `back_route`, `upload_path`, `auto_resize`, `quality`, `gallery`, `created`, `creator`, `modified`, `modified_by`) VALUES (1, 'Product', 'product_edit', 'product/', 1, 1, 0, NOW(), 'system', NOW(), 'system')");
        $this->addSql("INSERT INTO `image_setting` (`id`, `entity_name`, `back_route`, `upload_path`, `auto_resize`, `quality`, `gallery`, `created`, `creator`, `modified`, `modified_by`) VALUES (2, 'Project', 'project_edit', 'project/', 1, 1, 0, NOW(), 'system', NOW(), 'system')");
        $this->addSql("INSERT INTO `image_setting` (`id`, `entity_name`, `back_route`, `upload_path`, `auto_resize`, `quality`, `gallery`, `created`, `creator`, `modified`, `modified_by`) VALUES (3, 'Service', 'service_edit', 'service/', 1, 1, 0, NOW(), 'system', NOW(), 'system')");
        $this->addSql("INSERT INTO `image_setting` (`id`, `entity_name`, `back_route`, `upload_path`, `auto_resize`, `quality`, `gallery`, `created`, `creator`, `modified`, `modified_by`) VALUES (4, 'Blogger', 'blogger_edit', 'blogger/', 1, 1, 0, NOW(), 'system', NOW(), 'system')");
        $this->addSql("INSERT INTO `image_setting` (`id`, `entity_name`, `back_route`, `upload_path`, `auto_resize`, `quality`, `gallery`, `created`, `creator`, `modified`, `modified_by`) VALUES (5, 'Category', 'category_edit', 'category/', 1, 1, 0, NOW(), 'system', NOW(), 'system')");

        //IMAGE SETTING HAS TYPE
        /*******************************************************************************************************product************************************************************************************************************************/
        $this->addSql("INSERT INTO `image_setting_has_type` (`image_type_id`, `image_setting_id`, `radio_button`, `width`, `height`, `thumb_width`, `thumb_height`, `validate_width_and_height`, `validate_size`) VALUES (1, 1, 1, 625, 625, 140, 140, 0, 1)");
        $this->addSql("INSERT INTO `image_setting_has_type` (`image_type_id`, `image_setting_id`, `radio_button`, `width`, `height`, `thumb_width`, `thumb_height`, `validate_width_and_height`, `validate_size`) VALUES (2, 1, 0, 625, 625, NULL, NULL, 0, 1)");
        /*******************************************************************************************************project************************************************************************************************************************/
        $this->addSql("INSERT INTO `image_setting_has_type` (`image_type_id`, `image_setting_id`, `radio_button`, `width`, `height`, `thumb_width`, `thumb_height`, `validate_width_and_height`, `validate_size`) VALUES (1, 2, 1, 560, 360, NULL, NULL, 1, 1)");
        $this->addSql("INSERT INTO `image_setting_has_type` (`image_type_id`, `image_setting_id`, `radio_button`, `width`, `height`, `thumb_width`, `thumb_height`, `validate_width_and_height`, `validate_size`) VALUES (2, 2, 0, 630, 630, 140, 140, 1, 1)");
        /*******************************************************************************************************service************************************************************************************************************************/
        $this->addSql("INSERT INTO `image_setting_has_type` (`image_type_id`, `image_setting_id`, `radio_button`, `width`, `height`, `thumb_width`, `thumb_height`, `validate_width_and_height`, `validate_size`) VALUES (1, 3, 1, 350, 250, NULL, NULL, 1, 1)");
        $this->addSql("INSERT INTO `image_setting_has_type` (`image_type_id`, `image_setting_id`, `radio_button`, `width`, `height`, `thumb_width`, `thumb_height`, `validate_width_and_height`, `validate_size`) VALUES (3, 3, 1, 1720, 320, NULL, NULL, 0, 1)");
        /*******************************************************************************************************blog************************************************************************************************************************/
        $this->addSql("INSERT INTO `image_setting_has_type` (`image_type_id`, `image_setting_id`, `radio_button`, `width`, `height`, `thumb_width`, `thumb_height`, `validate_width_and_height`, `validate_size`) VALUES (1, 4, 1, 555, 360, NULL, NULL, 1, 1)");
        $this->addSql("INSERT INTO `image_setting_has_type` (`image_type_id`, `image_setting_id`, `radio_button`, `width`, `height`, `thumb_width`, `thumb_height`, `validate_width_and_height`, `validate_size`) VALUES (3, 4, 1, 1170, 600, NULL, NULL, 0, 1)");
        /*******************************************************************************************************category************************************************************************************************************************/
        $this->addSql("INSERT INTO `image_setting_has_type` (`image_type_id`, `image_setting_id`, `radio_button`, `width`, `height`, `thumb_width`, `thumb_height`, `validate_width_and_height`, `validate_size`) VALUES (1, 5, 1, 580, 580, 435, 275, 1, 1)");

        //ADMINISTRATOR
        $this->addSql("INSERT INTO `usr` (`id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `salt`, `password`, `last_login`, `confirmation_token`, `password_requested_at`, `roles`, `full_name`, `phone`, `facebook_id`, `created`, `creator`, `modified`, `modified_by`, `deleted`, `deleted_by`) VALUES (2, 'admin@seats.com', 'admin@seats.com', 'admin@seats.com', 'admin@seats.com', 1, NULL, '$2y$13\$U/J5HVKyDzIqRw1oPPDb4uj9DzCizmE3UfsCLmFhagHws2.a7jfKW', NULL, NULL, NULL, 'a:1:{i:0;s:10:\\\"ROLE_ADMIN\\\";}', 'admin', '+20 122 5262558', NULL, NOW(), 'system', NOW(), 'system', NULL, NULL)");


        //DYNAMIC CONTENT
        $this->addSql("INSERT INTO `dynamic_content` (`id`, `title`) VALUES (1, 'Home Page')");
        $this->addSql("INSERT INTO `dynamic_content` (`id`, `title`) VALUES (2, 'About Us Page')");
        $this->addSql("INSERT INTO `dynamic_content` (`id`, `title`) VALUES (3, 'Contact Us Page')");

        //DYNAMIC CONTENT ATTRIBUTE
        $this->addSql("INSERT INTO `dynamic_content_attribute` (`id`, `dynamic_content_id`, `title`, `value`, `type`, `hint`, `image_width`, `image_height`) VALUES (1, 1, 'Categories Section Title', 'Refresh Your Room', 1, NULL, NULL, NULL)");
        $this->addSql("INSERT INTO `dynamic_content_attribute` (`id`, `dynamic_content_id`, `title`, `value`, `type`, `hint`, `image_width`, `image_height`) VALUES (2, 1, 'Categories Section Description', 'Having a place set aside for an activity you love makes it more likely you’ll do it.', 2, NULL, NULL, NULL)");
        $this->addSql("INSERT INTO `dynamic_content_attribute` (`id`, `dynamic_content_id`, `title`, `value`, `type`, `hint`, `image_width`, `image_height`) VALUES (3, 2, 'Header Image', NULL, 4, '1920px * 1080px', 1920, 1080)");
        $this->addSql("INSERT INTO `dynamic_content_attribute` (`id`, `dynamic_content_id`, `title`, `value`, `type`, `hint`, `image_width`, `image_height`) VALUES (4, 2, 'Header Title', 'About Us', 1, NULL, NULL, NULL)");
        $this->addSql("INSERT INTO `dynamic_content_attribute` (`id`, `dynamic_content_id`, `title`, `value`, `type`, `hint`, `image_width`, `image_height`) VALUES (5, 2, 'Header Description', 'We believe in crafting pieces where sustainability and style go hand in hand. Made from materials like recycled', 2, NULL, NULL, NULL)");
        $this->addSql("INSERT INTO `dynamic_content_attribute` (`id`, `dynamic_content_id`, `title`, `value`, `type`, `hint`, `image_width`, `image_height`) VALUES (6, 2, 'About section title', 'About Seats', 1, NULL, NULL, NULL)");
        $this->addSql("INSERT INTO `dynamic_content_attribute` (`id`, `dynamic_content_id`, `title`, `value`, `type`, `hint`, `image_width`, `image_height`) VALUES (7, 2, 'About section description', 'Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur', 2, NULL, NULL, NULL)");
        $this->addSql("INSERT INTO `dynamic_content_attribute` (`id`, `dynamic_content_id`, `title`, `value`, `type`, `hint`, `image_width`, `image_height`) VALUES (8, 2, 'About section image', NULL, 4, '930px * 500px', 930, 500)");
        $this->addSql("INSERT INTO `dynamic_content_attribute` (`id`, `dynamic_content_id`, `title`, `value`, `type`, `hint`, `image_width`, `image_height`) VALUES (9, 2, 'Core values section title', 'Core Values', 1, NULL, NULL, NULL)");
        $this->addSql("INSERT INTO `dynamic_content_attribute` (`id`, `dynamic_content_id`, `title`, `value`, `type`, `hint`, `image_width`, `image_height`) VALUES (10, 2, 'Mission description', 'These are beautiful, well made & ortable! I bought them\r\nto wear to work & casually.', 2, NULL, NULL, NULL)");
        $this->addSql("INSERT INTO `dynamic_content_attribute` (`id`, `dynamic_content_id`, `title`, `value`, `type`, `hint`, `image_width`, `image_height`) VALUES (11, 2, 'Vision Description', 'These are beautiful, well made & ortable! I bought them\r\nto wear to work & casually.', 2, NULL, NULL, NULL)");
        $this->addSql("INSERT INTO `dynamic_content_attribute` (`id`, `dynamic_content_id`, `title`, `value`, `type`, `hint`, `image_width`, `image_height`) VALUES (12, 3, 'Address Title', 'Address', 1, NULL, NULL, NULL)");
        $this->addSql("INSERT INTO `dynamic_content_attribute` (`id`, `dynamic_content_id`, `title`, `value`, `type`, `hint`, `image_width`, `image_height`) VALUES (13, 3, 'Address Description', '5th Settlement\r\nCairo, Egypt', 2, NULL, NULL, NULL)");
        $this->addSql("INSERT INTO `dynamic_content_attribute` (`id`, `dynamic_content_id`, `title`, `value`, `type`, `hint`, `image_width`, `image_height`) VALUES (14, 3, 'Phone Numbers Title', 'Phone Numbers', 1, NULL, NULL, NULL)");
        $this->addSql("INSERT INTO `dynamic_content_attribute` (`id`, `dynamic_content_id`, `title`, `value`, `type`, `hint`, `image_width`, `image_height`) VALUES (15, 3, 'Phone Numbers Description', '+20 122 6245 698\r\n\r\n+20 108 7069 304', 2, NULL, NULL, NULL)");
        $this->addSql("INSERT INTO `dynamic_content_attribute` (`id`, `dynamic_content_id`, `title`, `value`, `type`, `hint`, `image_width`, `image_height`) VALUES (16, 3, 'Opening Times Title', 'We are Open', 1, NULL, NULL, NULL)");
        $this->addSql("INSERT INTO `dynamic_content_attribute` (`id`, `dynamic_content_id`, `title`, `value`, `type`, `hint`, `image_width`, `image_height`) VALUES (17, 3, 'Opening Times Description', 'Exchanges Every day 11am to 7pm\r\nexcept for the Fridays and Saturdays', 2, NULL, NULL, NULL)");
        $this->addSql("INSERT INTO `dynamic_content_attribute` (`id`, `dynamic_content_id`, `title`, `value`, `type`, `hint`, `image_width`, `image_height`) VALUES (18, 3, 'Social Media Links Title', 'Social Media', 1, NULL, NULL, NULL)");
        $this->addSql("INSERT INTO `dynamic_content_attribute` (`id`, `dynamic_content_id`, `title`, `value`, `type`, `hint`, `image_width`, `image_height`) VALUES (19, 3, 'Pinterest Link', 'https://www.pinterest.com/', 3, NULL, NULL, NULL)");
        $this->addSql("INSERT INTO `dynamic_content_attribute` (`id`, `dynamic_content_id`, `title`, `value`, `type`, `hint`, `image_width`, `image_height`) VALUES (20, 3, 'Facebook link', 'https://www.facebook.com/', 3, NULL, NULL, NULL)");
        $this->addSql("INSERT INTO `dynamic_content_attribute` (`id`, `dynamic_content_id`, `title`, `value`, `type`, `hint`, `image_width`, `image_height`) VALUES (21, 3, 'Instagram link', 'https://www.instagram.com/', 3, NULL, NULL, NULL)");
        $this->addSql("INSERT INTO `dynamic_content_attribute` (`id`, `dynamic_content_id`, `title`, `value`, `type`, `hint`, `image_width`, `image_height`) VALUES (22, 3, 'Twitter Link', 'https://www.twitter.com/', 3, NULL, NULL, NULL)");
        $this->addSql("INSERT INTO `dynamic_content_attribute` (`id`, `dynamic_content_id`, `title`, `value`, `type`, `hint`, `image_width`, `image_height`) VALUES (23, 3, 'We would love to hear from you title', 'We would love to hear from you.', 1, NULL, NULL, NULL)");
        $this->addSql("INSERT INTO `dynamic_content_attribute` (`id`, `dynamic_content_id`, `title`, `value`, `type`, `hint`, `image_width`, `image_height`) VALUES (24, 3, 'We would love to hear from you description', 'If you’ve got great products your making or looking to work with us then drop us a line.', 2, NULL, NULL, NULL)");

        // DYNAMIC CONTENT ATTRIBUTES TRANSLATIONS
        $this->addSql("INSERT INTO `dynamic_content_attribute_translations` (`translatable_id`, `language_id`, `value`) VALUES (1, 1, 'جدد غرفتك')");
        $this->addSql("INSERT INTO `dynamic_content_attribute_translations` (`translatable_id`, `language_id`, `value`) VALUES (2, 1, 'إن تخصيص مكان لنشاط تحبه يزيد من احتمالية قيامك به.')");
        $this->addSql("INSERT INTO `dynamic_content_attribute_translations` (`translatable_id`, `language_id`, `value`) VALUES (4, 1, 'عننا')");
        $this->addSql("INSERT INTO `dynamic_content_attribute_translations` (`translatable_id`, `language_id`, `value`) VALUES (5, 1, 'نحن نؤمن بصناعة القطع حيث تسير الاستدامة والأناقة جنبًا إلى جنب. مصنوعة من مواد مثل المعاد تدويرها')");
        $this->addSql("INSERT INTO `dynamic_content_attribute_translations` (`translatable_id`, `language_id`, `value`) VALUES (6, 1, 'حول Seats')");
        $this->addSql("INSERT INTO `dynamic_content_attribute_translations` (`translatable_id`, `language_id`, `value`) VALUES (7, 1, 'أنكايديديونتيوت لابوري ات دولار ماجنا أليكيوا . يوت انيم أد مينيم فينايم,كيواس نوستريد\r\nأكسير سيتاشن يللأمكو لابورأس نيسي يت أليكيوب أكس أيا كوممودو كونسيكيوات . ديواس\r\nأيوتي أريري دولار إن ريبريهينديرأيت فوليوبتاتي فيلايت أيسسي كايلليوم دولار أيو فيجايت\r\nنيولا باراياتيور. أيكسسيبتيور ساينت أوككايكات كيوبايداتات نون بروايدينت ,سيونت ان كيولبا\r\nكيو أوفيسيا ديسيريونتموليت انيم أيدي ايست لابوريوم')");
        $this->addSql("INSERT INTO `dynamic_content_attribute_translations` (`translatable_id`, `language_id`, `value`) VALUES (9, 1, 'القيم الجوهرية')");
        $this->addSql("INSERT INTO `dynamic_content_attribute_translations` (`translatable_id`, `language_id`, `value`) VALUES (10, 1, 'هذه جميلة وجيدة الصنع و ortable! اشتريتهم\r\nلارتدائه للعمل و عرضا.')");
        $this->addSql("INSERT INTO `dynamic_content_attribute_translations` (`translatable_id`, `language_id`, `value`) VALUES (11, 1, 'هذه جميلة وجيدة الصنع و ortable! اشتريتهم\r\nلارتدائه للعمل و عرضا.')");
        $this->addSql("INSERT INTO `dynamic_content_attribute_translations` (`translatable_id`, `language_id`, `value`) VALUES (12, 1, 'عنوان')");
        $this->addSql("INSERT INTO `dynamic_content_attribute_translations` (`translatable_id`, `language_id`, `value`) VALUES (13, 1, 'التجمع الخامس\r\nالقاهرة، مصر')");
        $this->addSql("INSERT INTO `dynamic_content_attribute_translations` (`translatable_id`, `language_id`, `value`) VALUES (14, 1, 'أرقام الهواتف')");
        $this->addSql("INSERT INTO `dynamic_content_attribute_translations` (`translatable_id`, `language_id`, `value`) VALUES (15, 1, '+20 122 6245 698\r\n\r\n+20 108 7069 304')");
        $this->addSql("INSERT INTO `dynamic_content_attribute_translations` (`translatable_id`, `language_id`, `value`) VALUES (16, 1, 'نحن متاحون')");
        $this->addSql("INSERT INTO `dynamic_content_attribute_translations` (`translatable_id`, `language_id`, `value`) VALUES (17, 1, 'التبادلات كل يوم 11:00 حتي 7:00\r\nباستثناء يومي الجمعة والسبت')");
        $this->addSql("INSERT INTO `dynamic_content_attribute_translations` (`translatable_id`, `language_id`, `value`) VALUES (18, 1, 'وسائل التواصل الاجتماعي')");
        $this->addSql("INSERT INTO `dynamic_content_attribute_translations` (`translatable_id`, `language_id`, `value`) VALUES (19, 1, 'https://www.pinterest.com/')");
        $this->addSql("INSERT INTO `dynamic_content_attribute_translations` (`translatable_id`, `language_id`, `value`) VALUES (20, 1, 'https://www.facebook.com/')");
        $this->addSql("INSERT INTO `dynamic_content_attribute_translations` (`translatable_id`, `language_id`, `value`) VALUES (21, 1, 'https://www.instagram.com/')");
        $this->addSql("INSERT INTO `dynamic_content_attribute_translations` (`translatable_id`, `language_id`, `value`) VALUES (22, 1, 'https://www.twitter.com/')");
        $this->addSql("INSERT INTO `dynamic_content_attribute_translations` (`translatable_id`, `language_id`, `value`) VALUES (23, 1, 'نحب أن نسمع منك.')");
        $this->addSql("INSERT INTO `dynamic_content_attribute_translations` (`translatable_id`, `language_id`, `value`) VALUES (24, 1, 'إذا كان لديك منتجات رائعة تصنعها أو تتطلع إلى العمل معنا ، فاتصل بنا.')");
    }

    public function postUp(Schema $schema) : void {
        //SEO BASE ROUTES
        $this->newSeoBaseRoute("Product", "product");
        $this->newSeoBaseRoute("Category", "category");
        $this->newSeoBaseRoute("Project", "project");
        $this->newSeoBaseRoute("Service", "service");
        $this->newSeoBaseRoute("Blogger", "blogger");
        $this->newSeoBaseRoute("SeoPage", "seo_page");

        // SEO
        $this->newSeo('Home Page', 'Home Page', 'home-page');
        $this->newSeo('Products Filter Page', 'Products Filter Page', 'products-filter-page');
        $this->newSeo('Blogs List Page', 'Blogs List Page Page', 'blogs-list-page');
        $this->newSeo('Services List Page', 'Services List Page', 'services-list-page');
        $this->newSeo('Projects List Page', 'Projects List Page', 'projects-list-page');
        $this->newSeo('About Us Page', 'About Us Page', 'about-us-page');
        $this->newSeo('Contact Us Page', 'Contact Us Page', 'contact-us-page');
    }

    private function newSeoBaseRoute($entityName, $baseRoute) {
        $em = $this->container->get('doctrine.orm.entity_manager');

        $seoBaseRoute  = new SeoBaseRoute();
        $seoBaseRoute->setEntityName($entityName);
        $seoBaseRoute->setBaseRoute($baseRoute);
        $seoBaseRoute->setCreated(new \DateTime());
        $seoBaseRoute->setCreator("System");
        $seoBaseRoute->setModified(new \DateTime());
        $seoBaseRoute->setModifiedBy("System");

        $em->persist($seoBaseRoute);
        $em->flush();
    }

    private function newSeo($seoPageTitle, $seoTitle, $seoSLug) {
        //getting entity manager
        $em = $this->container->get('doctrine.orm.entity_manager');

        //setting seo pages values
        $seoPage = new SeoPage();
        $seoPage->setTitle($seoPageTitle);
        $seoPage->setCreated(new \DateTime());
        $seoPage->setCreator('System');
        $seoPage->setModified(new \DateTime());
        $seoPage->setModifiedBy('System');

        //setting article seo values
        $seo = new Seo();
        $seo->setSeoBaseRoute($em->getRepository(SeoBaseRoute::class)->findByEntity($seoPage));
        $seo->setTitle($seoTitle);
        $seo->setSlug($seoSLug);
        $seo->setState(1);
        $seo->setLastModified(new \DateTime());

        $seoPage->setSeo($seo);

        $em->persist($seoPage);
        $em->persist($seo);

        $em->flush();
    }

    public function down(Schema $schema) : void
    {

    }
}
