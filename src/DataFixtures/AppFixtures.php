<?php

namespace App\DataFixtures;

use App\Entity\Post;
use App\Entity\User;
use App\Entity\Topic;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use Cocur\Slugify\Slugify;

class AppFixtures extends Fixture
{

    public function __construct (UserPasswordEncoderInterface $encoder) {
        $this->encoder = $encoder; 
        $this->slugify = new Slugify();
    }

    public function load(ObjectManager $manager)
    {
        $faker = \Faker\Factory::create();

        for ($i = 1; $i <= mt_rand(2, 10); $i++) {
            $user = new User(); 

            $hash = $this->encoder->encodePassword($user, 'pass');

            $userCA = $faker->dateTime(); 

            $user->setPseudo($faker->word)
                 ->setPassword($hash)
                 ->setDescription($faker->paragraph())
                 ->setCreatedAt($userCA); 

            for ($x = 1; $x <= mt_rand(2, 5); $x++) {
                $category = new Category(); 

                $category_name = $faker->word; 
                $category->setName($category_name)
                         ->setSlug($this->slugify->slugify($category_name)); 

                for ($j = 1; $j <= mt_rand(20, 30); $j++) {

                    $topic = new Topic(); 

                    $topic_title = $faker->sentence(1); 

                    $topic->setTitle($topic_title)
                          ->setContent(join($faker->paragraphs(), '\n'))
                          ->setUser($user)
                          ->setCategory($category)
                          ->setCreatedAt($faker->dateTimeBetween($userCA))
                          ->setSlug($this->slugify->slugify($topic_title)); 

                    
                    for ($a = 1; $a <= mt_rand(20, 30); $a++) {
                        $post = new Post(); 
    
                        $post->setContent($faker->paragraph())
                             ->setUser($user)
                             ->setTopic($topic)
                             ->setCreatedAt($faker->dateTimeBetween($userCA));
    

                        $manager->persist($post);
                    }
                    

                    $category->addTopic($topic);
                    
                    $manager->persist($topic); 
                }

                $manager->persist($category); 
            }

            $manager->persist($user); 
        }

        $manager->flush(); 
    }
}
