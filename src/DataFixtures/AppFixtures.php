<?php

// src/DataFixtures/AppFixtures.php

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
    /**
     * Injection du service de hashage des mots de passe.
     * Symfony 6+ utilise UserPasswordHasherInterface.
     */
    public function __construct(
        private UserPasswordHasherInterface $hasher
    ) {}

    /**
     * load() — Point d'entrée principal des fixtures
     *
     * L'ordre des sections est important car il respecte les dépendances :
     * SkinTypes → Users → Categories → Tags → Articles → Comments
     * → Routines → RoutineSteps → Newsletter
     */
    public function load(ObjectManager $manager): void
    {
        // ══════════════════════════════════════════════════════════════
        // 1. SKIN TYPES
        // Créés en premier car Users et Newsletter en dépendent
        // ══════════════════════════════════════════════════════════════

        $skinTypes    = [];
        $skinTypeData = [
            ['Normale',  'Peau équilibrée, ni trop grasse ni trop sèche.'],
            ['Sèche',    'Peau qui manque de lipides et d\'hydratation.'],
            ['Grasse',   'Peau avec excès de sébum, pores dilatés.'],
            ['Mixte',    'Zone T grasse, joues normales à sèches.'],
            ['Sensible', 'Peau réactive, sujette aux rougeurs et irritations.'],
        ];

        foreach ($skinTypeData as [$name, $desc]) {
            $st = new SkinType();
            $st->setName($name)->setDescription($desc);
            $manager->persist($st);
            $skinTypes[$name] = $st;
        }

        // ══════════════════════════════════════════════════════════════
        // 2. USERS
        // Un admin + deux auteures pour varier les articles
        // Les mots de passe sont hashés via UserPasswordHasherInterface
        // ══════════════════════════════════════════════════════════════

        // Compte administrateur — accès complet à l'interface EasyAdmin
        $admin = new User();
        $admin->setEmail('admin@mochiskin.com')
              ->setFirstName('Anjy')
              ->setLastName('CEO')
              ->setRoles(['ROLE_ADMIN', 'ROLE_USER'])
              ->setIsActive(true)
              ->setSkinType($skinTypes['Mixte'])
              ->setPassword($this->hasher->hashPassword($admin, 'admin123'));
        $manager->persist($admin);

        // Auteure 2 — rédactrice principale
        $jimin = new User();
        $jimin->setEmail('jimin@mochiskin.com')
              ->setFirstName('Ji-min')
              ->setLastName('Park')
              ->setRoles(['ROLE_USER'])
              ->setIsActive(true)
              ->setSkinType($skinTypes['Sèche'])
              ->setPassword($this->hasher->hashPassword($jimin, 'password123'));
        $manager->persist($jimin);

        // Auteure 3 — rédactrice secondaire
        $aminata = new User();
        $aminata->setEmail('aminata@mochiskin.com')
                ->setFirstName('Aminata')
                ->setLastName('Diop')
                ->setRoles(['ROLE_USER'])
                ->setIsActive(true)
                ->setSkinType($skinTypes['Sensible'])
                ->setPassword($this->hasher->hashPassword($aminata, 'password123'));
        $manager->persist($aminata);

        // ══════════════════════════════════════════════════════════════
        // 3. CATEGORIES
        // Indexées par slug pour être réutilisées dans les articles
        // ══════════════════════════════════════════════════════════════

        $categories   = [];
        $categoryData = [
            ['Routine',     'routine',     'Tout sur les routines skincare'],
            ['Ingrédients', 'ingredients', 'Décryptage des ingrédients cosmétiques'],
            ['Produits',    'produits',    'Tests et avis produits'],
            ['Conseils',    'conseils',    'Conseils pratiques pour prendre soin de sa peau'],
            ['DIY',         'diy',         'Recettes et soins maison'],
        ];

        foreach ($categoryData as [$name, $slug, $desc]) {
            $cat = new Category();
            $cat->setName($name)->setSlug($slug)->setDescription($desc);
            $manager->persist($cat);
            $categories[$slug] = $cat;
        }

        // ══════════════════════════════════════════════════════════════
        // 4. TAGS
        // Indexés par slug pour être réutilisés dans les articles
        // ══════════════════════════════════════════════════════════════

        $tags    = [];
        $tagData = [
            ['Vitamine C',         'vitamine-c'],
            ['Acide hyaluronique', 'acide-hyaluronique'],
            ['SPF',                'spf'],
            ['Double nettoyage',   'double-nettoyage'],
            ['Rétinol',            'retinol'],
            ['K-Beauty',           'k-beauty'],
            ['Hydratation',        'hydratation'],
            ['Anti-âge',           'anti-age'],
            ['Peaux sensibles',    'peaux-sensibles'],
            ['DIY',                'diy'],
        ];

        foreach ($tagData as [$name, $slug]) {
            $tag = new Tag();
            $tag->setName($name)->setSlug($slug);
            $manager->persist($tag);
            $tags[$slug] = $tag;
        }

        // ══════════════════════════════════════════════════════════════
        // 5. ARTICLES
        // Chaque article référence une catégorie, un auteur et des tags
        // ══════════════════════════════════════════════════════════════

        $articlesData = [
            [
                'title'    => 'Les 5 étapes d\'une routine matin parfaite',
                'slug'     => 'les-5-etapes-routine-matin-parfaite',
                'excerpt'  => 'Découvrez comment préparer votre peau chaque matin pour une journée éclatante.',
                'content'  => '<h2>Pourquoi une routine matin ?</h2>
<p>La routine matin prépare votre peau aux agressions de la journée : pollution, UV, stress.</p>
<h2>Étape 1 : Le nettoyant doux</h2>
<p>Commencez par un gel nettoyant doux. Massez 60 secondes, rincez à l\'eau tiède.</p>
<h2>Étape 2 : Le tonique</h2>
<p>Le tonique rééquilibre le pH et prépare l\'absorption des soins suivants.</p>
<h2>Étape 3 : Le sérum vitamine C</h2>
<p>Indispensable le matin ! La vitamine C illumine et protège contre les radicaux libres.</p>
<h2>Étape 4 : La crème hydratante</h2>
<p>Choisissez une crème légère adaptée à votre type de peau.</p>
<h2>Étape 5 : La protection solaire SPF50</h2>
<p>L\'étape la plus importante ! Renouvelez toutes les 2 heures en extérieur.</p>',
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
<p>Un sérum est un soin concentré en actifs qui cible des problématiques précises.</p>
<h2>Pour les peaux sèches</h2>
<p>Optez pour un sérum à l\'acide hyaluronique pour une hydratation intense.</p>
<h2>Pour les peaux grasses</h2>
<p>Choisissez un sérum à la niacinamide qui régule le sébum et resserre les pores.</p>
<h2>Pour les peaux ternes</h2>
<p>La vitamine C illumine le teint et efface les taches pigmentaires.</p>
<h2>Pour les peaux matures</h2>
<p>Le rétinol accélère le renouvellement cellulaire. Commencez avec une faible concentration.</p>',
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
                'excerpt'  => 'Le double nettoyage est une technique coréenne incontournable pour une peau nette.',
                'content'  => '<h2>Qu\'est-ce que le double nettoyage ?</h2>
<p>Originaire de Corée du Sud : d\'abord une huile, puis un nettoyant moussant.</p>
<h2>Pourquoi deux étapes ?</h2>
<p>L\'huile dissout les impuretés liposolubles, le nettoyant aqueux élimine les résidus hydrosolubles.</p>
<h2>Étape 1 : L\'huile démaquillante</h2>
<p>Appliquez sur peau sèche, massez 60 secondes, émulsionnez avec de l\'eau, rincez.</p>
<h2>Étape 2 : Le nettoyant moussant</h2>
<p>Massez 30 secondes supplémentaires. Rincez à l\'eau tiède.</p>',
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
<p>Une molécule naturelle qui peut retenir jusqu\'à 1000 fois son poids en eau.</p>
<h2>Les différents poids moléculaires</h2>
<p>Les molécules légères pénètrent plus profondément, les lourdes forment un film protecteur.</p>
<h2>Comment l\'utiliser ?</h2>
<p>Appliquez sur peau légèrement humide, puis scellez avec une crème hydratante.</p>',
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
<p>Écrasez un avocat mûr avec du miel. Laissez poser 15 minutes.</p>
<h2>Masque à l\'argile pour peau grasse</h2>
<p>Argile verte + eau florale de lavande. Rincez avant séchage complet.</p>
<h2>Masque au miel et curcuma pour l\'éclat</h2>
<p>Miel + curcuma + citron. Attention aux taches sur les vêtements !</p>',
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
<p>La nuit, votre peau se régénère et absorbe mieux les actifs.</p>
<h2>Le double nettoyage</h2>
<p>Huile démaquillante puis gel moussant. Une peau propre absorbe mieux les soins.</p>
<h2>Le sérum réparateur</h2>
<p>Rétinol ou acides AHA/BHA : commencez 2 fois par semaine.</p>
<h2>La crème de nuit</h2>
<p>Plus riche que la crème de jour, elle nourrit en profondeur pendant le sommeil.</p>',
                'category' => 'routine',
                'author'   => $jimin,
                'tags'     => ['retinol', 'anti-age', 'k-beauty'],
                'reading'  => 6,
                'views'    => 201,
                'date'     => '2026-01-18',
            ],
        ];

        // Boucle de création des articles
        // Les tags sont ajoutés via addTag() (relation ManyToMany)
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

        // ══════════════════════════════════════════════════════════════
        // 6. COMMENTAIRES
        // Liés aux articles et aux auteurs créés plus haut
        // isApproved = false → commentaire en attente de modération
        // ══════════════════════════════════════════════════════════════

        $commentsData = [
            [$articles[0], $jimin,   'Super article ! Ma peau est transformée depuis 2 semaines.',                              true],
            [$articles[0], $aminata, 'Je ne savais pas que le SPF était aussi important en hiver. Merci !',                    true],
            [$articles[1], $admin,   'Je recommande le sérum à la niacinamide pour les peaux mixtes.',                         true],
            [$articles[2], $jimin,   'Le double nettoyage a changé ma vie ! Ma peau n\'a jamais été aussi propre.',            true],
            [$articles[3], $aminata, 'Article très complet. J\'aurais aimé connaître l\'acide hyaluronique plus tôt !',        true],
            [$articles[4], $admin,   'Le masque à l\'avocat est devenu mon rituel du dimanche soir. Merci !',                  true],
            [$articles[5], $jimin,   'La routine soir fait vraiment la différence. Mon rétinol est enfin bien intégré.',       false],
        ];

        foreach ($commentsData as [$article, $author, $content, $approved]) {
            $comment = new Comment();
            $comment->setContent($content)
                    ->setArticle($article)
                    ->setAuthor($author)
                    ->setIsApproved($approved);
            $manager->persist($comment);
        }

        // ══════════════════════════════════════════════════════════════
        // 7. ROUTINES & ROUTINE STEPS
        // Deux routines : matin (morning) et soir (evening)
        // Chaque routine contient plusieurs étapes ordonnées
        // ══════════════════════════════════════════════════════════════

        // ── Routine Matin ─────────────────────────────────────────────
        $morningRoutine = new Routine();
        $morningRoutine->setType('morning')
                       ->setName('Routine Matin MOCHISKIN')
                       ->setDescription('La routine matin complète pour préparer et protéger votre peau.')
                       ->setDurationMinutes(10)
                       ->setStepCount(5);
        $manager->persist($morningRoutine);

        // Étapes : [ordre, titre, description, produit recommandé]
        $morningSteps = [
            [1, 'Nettoyant doux',     'Éliminez les impuretés de la nuit en massant 60 secondes.',                             'Gel nettoyant purifiant'],
            [2, 'Tonique hydratant',  'Rééquilibrez le pH et préparez l\'absorption des soins suivants.',                      'Tonique sans alcool'],
            [3, 'Sérum vitamine C',   'Illuminez le teint et protégez contre les radicaux libres avec quelques gouttes.',      'Sérum éclat vitamine C 20%'],
            [4, 'Crème hydratante',   'Maintenez l\'hydratation toute la journée avec une crème légère.',                     'Crème hydratante légère'],
            [5, 'Protection solaire', 'SPF50 obligatoire même en hiver. Renouvelez toutes les 2h en extérieur.',               'Protection solaire SPF50+'],
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

        // ── Routine Soir ──────────────────────────────────────────────
        $eveningRoutine = new Routine();
        $eveningRoutine->setType('evening')
                       ->setName('Routine Soir MOCHISKIN')
                       ->setDescription('La routine soir complète pour réparer et régénérer votre peau pendant la nuit.')
                       ->setDurationMinutes(15)
                       ->setStepCount(7);
        $manager->persist($eveningRoutine);

        // Étapes : [ordre, titre, description, produit recommandé]
        $eveningSteps = [
            [1, 'Huile démaquillante',        '1ère étape du double nettoyage. Dissout maquillage et impuretés liposolubles.',  'Huile démaquillante douce'],
            [2, 'Nettoyant moussant',          '2ème étape du double nettoyage. Nettoie la peau en profondeur.',               'Mousse nettoyante purifiante'],
            [3, 'Exfoliant doux (2x/semaine)', 'Deux fois par semaine, éliminez les cellules mortes.',                         'Exfoliant enzymatique'],
            [4, 'Tonique apaisant',            'Rééquilibrez le pH pour préparer la peau aux actifs suivants.',                'Tonique hydratant et apaisant'],
            [5, 'Sérum réparateur',            'Privilégiez rétinol, acide hyaluronique ou peptides selon vos besoins.',       'Sérum anti-âge rétinol'],
            [6, 'Contour des yeux',            'Appliquez en tapotant sur cette zone fragile.',                                'Contour yeux anti-cernes'],
            [7, 'Crème de nuit',               'Nourrissez et régénérez pendant le sommeil avec une crème riche.',            'Crème de nuit régénérante'],
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

        // ══════════════════════════════════════════════════════════════
        // 8. NEWSLETTER
        // Chaque abonné a un type de peau, des préoccupations (concerns)
        // et des centres d'intérêt (interests) associés
        // ══════════════════════════════════════════════════════════════

        // Format : [prénom, email, type de peau, concerns[], interests[]]
        $newsletterData = [
            ['Sophie', 'sophie@exemple.com', 'Sèche',    ['hydratation', 'peaux-sensibles'], ['k-beauty', 'naturel']],
            ['Marie',  'marie@exemple.com',  'Mixte',    ['pores', 'acne'],                  ['ingredients', 'tests']],
            ['Julie',  'julie@exemple.com',  'Normale',  ['rides'],                          ['anti-age', 'diy']],
            ['Laura',  'laura@exemple.com',  'Sensible', ['rougeurs'],                       ['naturel', 'diy']],
        ];

        foreach ($newsletterData as [$firstName, $email, $skinTypeName, $concerns, $interests]) {
            $newsletter = new Newsletter();
            $newsletter->setFirstName($firstName)
                       ->setEmail($email)
                       ->setIsActive(true)
                       ->setSkinType($skinTypes[$skinTypeName]);
            $manager->persist($newsletter);

            // Préoccupations skin (ex: pores, acné, rides...)
            foreach ($concerns as $concern) {
                $nc = new NewsletterConcern();
                $nc->setConcern($concern)->setNewsletter($newsletter);
                $manager->persist($nc);
            }

            // Centres d'intérêt (ex: k-beauty, DIY, ingrédients...)
            foreach ($interests as $interest) {
                $ni = new NewsletterInterest();
                $ni->setInterest($interest)->setNewsletter($newsletter);
                $manager->persist($ni);
            }
        }

        // ══════════════════════════════════════════════════════════════
        // FLUSH FINAL
        // Un seul flush à la fin pour optimiser les performances
        // Doctrine envoie toutes les requêtes SQL en une seule transaction
        // ══════════════════════════════════════════════════════════════

        $manager->flush();
    }
}