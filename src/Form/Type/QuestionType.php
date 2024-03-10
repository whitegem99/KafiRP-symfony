<?php declare(strict_types=1);

namespace App\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class QuestionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('status', ChoiceType::class, [
                'label' => 'Yayında mı?',
                'expanded' => true,
                'multiple' => false,
                'choices' => [
                    'Evet' => 1,
                    'Hayır' => 0
                ]
            ])
            ->add('random', ChoiceType::class, [
                'label' => 'Rastgele gösterilecek bir soru mu?',
                'help' => 'Başvuru formunda en fazla 3 adet rastgele soru gösterilir. Bu kutucuğu işaretlerseniz bu soru sadece o 3 sorudan biri olarak başvurana gösterilebilir.',
                'expanded' => true,
                'multiple' => false,
                'choices' => [
                    'Evet' => 1,
                    'Hayır' => 0
                ]
            ])
            ->add('textQuestion', EntityType::class, [
                'class' => \App\Entity\TextQuestion::class,
                'placeholder' => 'Lütfen Seçiniz',
                'choice_label' => 'text',
                'label' => 'Metin Sorusu',
                'required' => false,
            ])
            ->add('choiceQuestion', EntityType::class, [
                'class' => \App\Entity\ChoiceQuestion::class,
                'placeholder' => 'Lütfen Seçiniz',
                'choice_label' => 'text',
                'label' => 'Çoktan Seçmeli Sorusu',
                'required' => false,
            ])
            ->add('sort', IntegerType::class, ['label' => 'Soru Sırası'])
            ->add('save', SubmitType::class, ['label' => 'Kaydet'])
        ;
    }

//    public function configureOptions(OptionsResolver $resolver): void
//    {
//        $resolver->setDefaults([
//            'require_password' => true,
//        ]);
//
//        $resolver->setAllowedTypes('require_password', 'bool');
//    }
}