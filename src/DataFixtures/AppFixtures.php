<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Newsletter;
use App\Entity\NewsletterConcern;
use App\Entity\NewsletterInterest;
use App\Entity\Routine;
use App\Entity\RoutineStep;
use App\Entity\SkinType;
use App\Entity\Tag;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $hasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        // ══════════════════════════════════════════
        // 1. SKIN TYPES
        // ══════════════════════════════════════════
        $skinTypes = [];
        $skinTypeData = [
            ['Normale',   'Peau équilibrée, ni trop grasse ni trop sèche.'],
            ['Sèche',     'Peau qui manque de lipides et d\'hydratation.'],
            ['Grasse',    'Peau avec excès de sébum, pores dilatés.'],
            ['Mixte',     'Zone T grasse, joues normales à sèches.'],
            ['Sensible',  'Peau réactive, sujette aux rougeurs et irritations.'],
        ];

        foreach ($skinTypeData as [$name, $desc]) {
            $st = new SkinType();
            $st->setName($name)->setDescription($desc);
            $manager->persist($st);
            $skinTypes[$name] = $st;
        }

        // ══════════════════════════════════════════
        // 2. USERS
        // ══════════════════════════════════════════

        // Admin
        $admin = new User();
        $admin->setEmail('admin@mochiskin.com')
              ->setFirstName('Anjy')
              ->setLastName('CEO')
              ->setRoles(['ROLE_ADMIN', 'ROLE_USER'])
              ->setIsActive(true)
              ->setSkinType($skinTypes['Mixte'])
              ->setPassword($this->hasher->hashPassword($admin, 'admin123'));
        $manager->persist($admin);

        // Auteure 2
        $jimin = new User();
        $jimin->setEmail('jimin@mochiskin.com')
              ->setFirstName('Ji-min')
              ->setLastName('Park')
              ->setRoles(['ROLE_USER'])
              ->setIsActive(true)
              ->setSkinType($skinTypes['Sèche'])
              ->setPassword($this->hasher->hashPassword($jimin, 'password123'));
        $manager->persist($jimin);

        // Auteure 3
        $aminata = new User();
        $aminata->setEmail('aminata@mochiskin.com')
                ->setFirstName('Aminata')
                ->setLastName('Diop')
                ->setRoles(['ROLE_USER'])
                ->setIsActive(true)
                ->setSkinType($skinTypes['Sensible'])
                ->setPassword($this->hasher->hashPassword($aminata, 'password123'));
        $manager->persist($aminata);

        // ══════════════════════════════════════════
        // 3. CATEGORIES
        // ══════════════════════════════════════════
        $categories = [];
        $categoryData = [
            ['Routine',      'routine',      'Tout sur les routines skincare'],
            ['Ingrédients',  'ingredients',  'Décryptage des ingrédients cosmétiques'],
            ['Produits',     'produits',     'Tests et avis produits'],
            ['Conseils',     'conseils',     'Conseils pratiques pour prendre soin de sa peau'],
            ['DIY',          'diy',          'Recettes et soins maison'],
        ];

        foreach ($categoryData as [$name, $slug, $desc]) {
            $cat = new Category();
            $cat->setName($name)->setSlug($slug)->setDescription($desc);
            $manager->persist($cat);
            $categories[$slug] = $cat;
        }

        // ══════════════════════════════════════════
        // 4. TAGS
        // ══════════════════════════════════════════
        $tags = [];
        $tagData = [
            ['Vitamine C',        'vitamine-c'],
            ['Acide hyaluronique','acide-hyaluronique'],
            ['SPF',               'spf'],
            ['Double nettoyage',  'double-nettoyage'],
            ['Rétinol',           'retinol'],
            ['K-Beauty',          'k-beauty'],
            ['Hydratation',       'hydratation'],
            ['Anti-âge',          'anti-age'],
            ['Peaux sensibles',   'peaux-sensibles'],
            ['DIY',               'diy'],
        ];

        foreach ($tagData as [$name, $slug]) {
            $tag = new Tag();
            $tag->setName($name)->setSlug($slug);
            $manager->persist($tag);
            $tags[$slug] = $tag;
        }

        // ══════════════════════════════════════════
        // 5. ARTICLES
        // ══════════════════════════════════════════
        $articlesData = [
            [
                'title'    => 'Les 5 étapes d\'une routine matin parfaite',
                'slug'     => 'les-5-etapes-routine-matin-parfaite',
                'excerpt'  => 'Découvrez comment préparer votre peau chaque matin pour une journée éclatante.',
                'content'  => '<h2>Pourquoi une routine matin ?</h2>
<p>La routine matin prépare votre peau aux agressions de la journée : pollution, UV, stress. Une peau bien protégée reste saine plus longtemps.</p>
<h2>Étape 1 : Le nettoyant doux</h2>
<p>Commencez par un gel nettoyant doux pour éliminer les impuretés accumulées pendant la nuit. Massez en mouvements circulaires pendant 60 secondes, puis rincez à l\'eau tiède.</p>
<h2>Étape 2 : Le tonique</h2>
<p>Le tonique rééquilibre le pH de votre peau et prépare l\'absorption des soins suivants. Appliquez avec un coton ou directement avec les mains.</p>
<h2>Étape 3 : Le sérum vitamine C</h2>
<p>Indispensable le matin ! La vitamine C illumine le teint et protège contre les radicaux libres. Quelques gouttes suffisent.</p>
<h2>Étape 4 : La crème hydratante</h2>
<p>Choisissez une crème légère adaptée à votre type de peau pour maintenir l\'hydratation toute la journée.</p>
<h2>Étape 5 : La protection solaire SPF50</h2>
<p>L\'étape la plus importante ! Même en hiver, les UV abîment la peau. Appliquez généreusement et renouvelez toutes les 2 heures en extérieur.</p>',
                'category' => 'routine',
                'author'   => $jimin,
                'tags'     => ['vitamine-c', 'spf', 'k-beauty'],
                'reading'  => 5,
                'views'    => 342,
                'date'     => '2025-01-15',
            ],
            [
                'title'    => 'Comment choisir son sérum selon son type de peau',
                'slug'     => 'comment-choisir-serum-type-peau',
                'excerpt'  => 'Le sérum est l\'étape clé de votre routine. Voici comment le choisir selon votre type de peau.',
                'content'  => '<h2>Qu\'est-ce qu\'un sérum ?</h2>
<p>Un sérum est un soin concentré en actifs qui cible des problématiques précises. Sa texture légère permet une pénétration rapide dans les couches profondes de la peau.</p>
<h2>Pour les peaux sèches</h2>
<p>Optez pour un sérum à l\'acide hyaluronique. Il attire et retient l\'eau dans les tissus cutanés, offrant une hydratation intense et durable.</p>
<h2>Pour les peaux grasses</h2>
<p>Choisissez un sérum à la niacinamide qui régule le sébum, resserre les pores et uniformise le teint sans alourdir la peau.</p>
<h2>Pour les peaux ternes</h2>
<p>La vitamine C est votre alliée. Elle stimule la synthèse du collagène, illumine le teint et efface les taches pigmentaires progressivement.</p>
<h2>Pour les peaux matures</h2>
<p>Le rétinol accélère le renouvellement cellulaire et stimule la production de collagène. Commencez avec une faible concentration et augmentez progressivement.</p>',
                'category' => 'produits',
                'author'   => $jimin,
                'tags'     => ['acide-hyaluronique', 'vitamine-c', 'retinol'],
                'reading'  => 7,
                'views'    => 218,
                'date'     => '2025-01-12',
            ],
            [
                'title'    => 'Les bienfaits du double nettoyage coréen',
                'slug'     => 'bienfaits-double-nettoyage-coreen',
                'excerpt'  => 'Le double nettoyage est une technique coréenne incontournable pour une peau nette et éclatante.',
                'content'  => '<h2>Qu\'est-ce que le double nettoyage ?</h2>
<p>Originaire de Corée du Sud, le double nettoyage consiste à utiliser deux produits successifs : d\'abord une huile ou un baume, puis un nettoyant moussant à base d\'eau.</p>
<h2>Pourquoi deux étapes ?</h2>
<p>La règle est simple : "like dissolves like". L\'huile dissout les impuretés liposolubles (maquillage, crème solaire, sébum), tandis que le nettoyant aqueux élimine les résidus hydrosolubles (sueur, pollution).</p>
<h2>Étape 1 : L\'huile démaquillante</h2>
<p>Appliquez l\'huile sur peau sèche et massez délicatement pendant 60 secondes. Ajoutez un peu d\'eau pour émulsionner, puis rincez. La peau doit être propre mais pas tiraillante.</p>
<h2>Étape 2 : Le nettoyant moussant</h2>
<p>Appliquez le second nettoyant et massez pendant 30 secondes supplémentaires. Rincez à l\'eau tiède. Votre peau est maintenant parfaitement propre et prête à recevoir vos soins.</p>',
                'category' => 'conseils',
                'author'   => $admin,
                'tags'     => ['double-nettoyage', 'k-beauty'],
                'reading'  => 6,
                'views'    => 189,
                'date'     => '2025-01-08',
            ],
            [
                'title'    => 'L\'acide hyaluronique : tout ce que vous devez savoir',
                'slug'     => 'acide-hyaluronique-tout-savoir',
                'excerpt'  => 'Tout ce que vous devez savoir sur cet ingrédient star de l\'hydratation cutanée.',
                'content'  => '<h2>Qu\'est-ce que l\'acide hyaluronique ?</h2>
<p>L\'acide hyaluronique est une molécule naturellement présente dans notre organisme. Sa particularité ? Elle peut retenir jusqu\'à 1000 fois son poids en eau, ce qui en fait un hydratant exceptionnel.</p>
<h2>Les différents poids moléculaires</h2>
<p>Il existe plusieurs types d\'acide hyaluronique selon leur taille moléculaire. Les molécules légères pénètrent plus profondément dans la peau, tandis que les molécules lourdes forment un film protecteur en surface.</p>
<h2>Comment l\'utiliser ?</h2>
<p>Appliquez le sérum sur peau légèrement humide pour maximiser son effet. Superposez ensuite une crème hydratante pour sceller l\'humidité. Utilisez matin et soir pour des résultats optimaux.</p>',
                'category' => 'ingredients',
                'author'   => $aminata,
                'tags'     => ['acide-hyaluronique', 'hydratation'],
                'reading'  => 8,
                'views'    => 275,
                'date'     => '2025-01-05',
            ],
            [
                'title'    => '3 masques maison faciles à faire soi-même',
                'slug'     => '3-masques-maison-faciles',
                'excerpt'  => 'Réalisez vos propres masques avec des ingrédients naturels du quotidien.',
                'content'  => '<h2>Masque à l\'avocat pour peau sèche</h2>
<p>Écrasez la moitié d\'un avocat mûr et mélangez avec une cuillère à soupe de miel. Appliquez sur le visage et laissez poser 15 minutes. L\'avocat nourrit intensément la peau grâce à ses acides gras.</p>
<h2>Masque à l\'argile pour peau grasse</h2>
<p>Mélangez 2 cuillères d\'argile verte avec de l\'eau florale de lavande jusqu\'à obtenir une pâte lisse. Appliquez en couche fine et laissez sécher 10 minutes. Rincez avant que l\'argile soit complètement sèche.</p>
<h2>Masque au miel et curcuma pour l\'éclat</h2>
<p>Mélangez une cuillère de miel avec une pincée de curcuma et quelques gouttes de jus de citron. Le miel hydrate, le curcuma illumine et le citron resserre les pores. Attention aux taches sur les vêtements !</p>',
                'category' => 'diy',
                'author'   => $aminata,
                'tags'     => ['diy', 'hydratation'],
                'reading'  => 5,
                'views'    => 156,
                'date'     => '2026-01-02',
            ],
            [
                'title'    => 'Routine du soir : les essentiels pour une peau régénérée',
                'slug'     => 'routine-soir-essentiels-peau-regeneree',
                'excerpt'  => 'Le soir, votre peau a besoin d\'une attention particulière pour se régénérer.',
                'content'  => '<h2>Pourquoi la routine soir est cruciale ?</h2>
<p>La nuit, votre peau entre en mode réparation. Elle se régénère, élimine les toxines et absorbe mieux les actifs. C\'est le moment idéal pour appliquer vos soins les plus concentrés.</p>
<h2>Le double nettoyage</h2>
<p>Commencez toujours par démaquiller avec une huile, puis nettoyez avec un gel moussant. Une peau propre absorbe mieux les soins qui suivent.</p>
<h2>Le sérum réparateur</h2>
<p>Le soir, vous pouvez utiliser des actifs plus puissants comme le rétinol ou les acides AHA/BHA. Commencez par une application 2 fois par semaine pour habituer votre peau.</p>
<h2>La crème de nuit</h2>
<p>Plus riche que la crème de jour, elle nourrit en profondeur et soutient le processus de régénération cellulaire nocturne. Appliquez en dernier, sur peau légèrement humide.</p>',
                'category' => 'routine',
                'author'   => $jimin,
                'tags'     => ['retinol', 'anti-age', 'k-beauty'],
                'reading'  => 6,
                'views'    => 201,
                'date'     => '2026-01-18',
            ],
        ];

        $articles = [];
        foreach ($articlesData as $data) {
            $article = new Article();
            $article->setTitle($data['title'])
                    ->setSlug($data['slug'])
                    ->setExcerpt($data['excerpt'])
                    ->setContent($data['content'])
                    ->setCategory($categories[$data['category']])
                    ->setAuthor($data['author'])
                    ->setReadingTime($data['reading'])
                    ->setViewCount($data['views'])
                    ->setPublishedAt(new \DateTimeImmutable($data['date']))
                    ->setUpdatedAt(new \DateTimeImmutable($data['date']));

            foreach ($data['tags'] as $tagSlug) {
                $article->addTag($tags[$tagSlug]);
            }

            $manager->persist($article);
            $articles[] = $article;
        }

        // ══════════════════════════════════════════
        // 6. COMMENTAIRES
        // ══════════════════════════════════════════
        $commentsData = [
            [$articles[0], $jimin,   'Super article ! J\'ai adopté cette routine depuis 2 semaines et ma peau est transformée.', true],
            [$articles[0], $aminata, 'Merci pour ces conseils ! Je ne savais pas que le SPF était aussi important en hiver.', true],
            [$articles[1], $admin,   'Très utile pour choisir le bon sérum. Je recommande le sérum à la niacinamide pour les peaux mixtes.', true],
            [$articles[2], $jimin,   'Le double nettoyage a changé ma vie ! Ma peau n\'a jamais été aussi propre.', true],
            [$articles[3], $aminata, 'Article très complet sur l\'acide hyaluronique. J\'aurais aimé connaître ça plus tôt !', true],
            [$articles[4], $admin,   'Le masque à l\'avocat est devenu mon rituel du dimanche soir. Merci !', true],
            [$articles[5], $jimin,   'La routine soir fait vraiment la différence. Mon rétinol est enfin bien intégré.', false],
        ];

        foreach ($commentsData as [$article, $author, $content, $approved]) {
            $comment = new Comment();
            $comment->setContent($content)
                    ->setArticle($article)
                    ->setAuthor($author)
                    ->setIsApproved($approved);
            $manager->persist($comment);
        }

        // ══════════════════════════════════════════
        // 7. ROUTINES
        // ══════════════════════════════════════════

        // Routine Matin
        $morningRoutine = new Routine();
        $morningRoutine->setType('morning')
                       ->setName('Routine Matin MOCHISKIN')
                       ->setDescription('La routine matin complète pour préparer et protéger votre peau.')
                       ->setDurationMinutes(10)
                       ->setStepCount(5);
        $manager->persist($morningRoutine);

        $morningSteps = [
            [1, 'Nettoyant doux',      'Nettoyez votre visage avec un gel nettoyant doux pour éliminer les impuretés de la nuit.',          'Gel nettoyant purifiant'],
            [2, 'Tonique hydratant',   'Rééquilibrez le pH de votre peau et préparez l\'absorption des soins suivants.',                    'Tonique sans alcool'],
            [3, 'Sérum vitamine C',    'Illuminez votre teint et protégez votre peau des radicaux libres avec quelques gouttes.',            'Sérum éclat vitamine C 20%'],
            [4, 'Crème hydratante',    'Maintenez l\'hydratation toute la journée avec une crème légère adaptée à votre type de peau.',      'Crème hydratante légère'],
            [5, 'Protection solaire',  'L\'étape la plus importante ! SPF50 obligatoire même en hiver, renouvelez toutes les 2h en extérieur.', 'Protection solaire SPF50+'],
        ];

        foreach ($morningSteps as [$order, $title, $desc, $product]) {
            $step = new RoutineStep();
            $step->setOrderNumber($order)
                 ->setTitle($title)
                 ->setDescription($desc)
                 ->setProductRecommendation($product)
                 ->setRoutine($morningRoutine);
            $manager->persist($step);
        }

        // Routine Soir
        $eveningRoutine = new Routine();
        $eveningRoutine->setType('evening')
                       ->setName('Routine Soir MOCHISKIN')
                       ->setDescription('La routine soir complète pour réparer et régénérer votre peau pendant la nuit.')
                       ->setDurationMinutes(15)
                       ->setStepCount(7);
        $manager->persist($eveningRoutine);

        $eveningSteps = [
            [1, 'Huile démaquillante',        '1ère étape du double nettoyage. L\'huile dissout le maquillage et les impuretés liposolubles.',       'Huile démaquillante douce'],
            [2, 'Nettoyant moussant',          '2ème étape du double nettoyage. Élimine les résidus et nettoie la peau en profondeur.',              'Mousse nettoyante purifiante'],
            [3, 'Exfoliant doux (2x/semaine)', 'Deux fois par semaine, éliminez les cellules mortes pour affiner le grain de peau.',                 'Exfoliant enzymatique'],
            [4, 'Tonique apaisant',            'Rééquilibrez le pH avec un tonique apaisant pour préparer la peau aux actifs suivants.',             'Tonique hydratant et apaisant'],
            [5, 'Sérum réparateur',            'Le soir, privilégiez rétinol, acide hyaluronique ou peptides selon vos besoins.',                   'Sérum anti-âge rétinol'],
            [6, 'Contour des yeux',            'Appliquez délicatement en tapotant un soin spécifique pour cette zone fragile.',                     'Contour yeux anti-cernes'],
            [7, 'Crème de nuit',               'Terminez avec une crème riche pour nourrir et régénérer pendant le sommeil.',                       'Crème de nuit régénérante'],
        ];

        foreach ($eveningSteps as [$order, $title, $desc, $product]) {
            $step = new RoutineStep();
            $step->setOrderNumber($order)
                 ->setTitle($title)
                 ->setDescription($desc)
                 ->setProductRecommendation($product)
                 ->setRoutine($eveningRoutine);
            $manager->persist($step);
        }

        // ══════════════════════════════════════════
        // 8. NEWSLETTER
        // ══════════════════════════════════════════
        $newsletterData = [
            ['Sophie',  'sophie@exemple.com',  'Sèche',    ['hydratation', 'peaux-sensibles'], ['k-beauty', 'naturel']],
            ['Marie',   'marie@exemple.com',   'Mixte',    ['pores', 'acne'],                  ['ingredients', 'tests']],
            ['Julie',   'julie@exemple.com',   'Normale',  ['rides'],                          ['anti-age', 'diy']],
            ['Laura',   'laura@exemple.com',   'Sensible', ['rougeurs'],                       ['naturel', 'diy']],
        ];

        foreach ($newsletterData as [$firstName, $email, $skinTypeName, $concerns, $interests]) {
            $newsletter = new Newsletter();
            $newsletter->setFirstName($firstName)
                       ->setEmail($email)
                       ->setIsActive(true)
                       ->setSkinType($skinTypes[$skinTypeName]);

            $manager->persist($newsletter);

            foreach ($concerns as $concern) {
                $nc = new NewsletterConcern();
                $nc->setConcern($concern)->setNewsletter($newsletter);
                $manager->persist($nc);
            }

            foreach ($interests as $interest) {
                $ni = new NewsletterInterest();
                $ni->setInterest($interest)->setNewsletter($newsletter);
                $manager->persist($ni);
            }
        }

        $manager->flush();
    }
}