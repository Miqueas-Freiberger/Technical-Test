<?php

namespace AppBundle\Form;

use AppBundle\Entity\Article;
use Doctrine\DBAL\Types\StringType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', null, array(
                'attr' =>  array('placeholder' => "Title"),
                'label'=> false
            ))
            ->add('description', TextareaType::class, array(
                'attr' =>  array('placeholder' => "Description"),
                'label'=> false
            ))
            ->add('pictureRoute', FileType::class, array(
                'attr' =>  array('placeholder' => false),
                'label'=> false
            ))
            ->add('save', SubmitType::class, ['label' => 'Create Article']);
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
