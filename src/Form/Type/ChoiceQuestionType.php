<?php declare(strict_types=1);

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChoiceQuestionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('text', TextareaType::class, ['label' => 'Soru'])
            ->add('helpText', TextType::class, ['label' => 'Yardımcı Metin', 'required' => false])
            ->add('isCheckbox', ChoiceType::class, [
                'label' => 'Çok seçmeli mi?',
                'expanded' => true,
                'multiple' => false,
                'choices' => [
                    'Hayır' => 0,
                    'Evet' => 1
                ]
            ])
            ->add('options', CollectionType::class, [
                'label' => 'Seçenekler',
                'entry_type' => OptionType::class,
                'entry_options' => [
                    'label' => false,
                ],
                'by_reference' => false,
                // this allows the creation of new forms and the prototype too
                'allow_add' => true,
                // self explanatory, this one allows the form to be removed
                'allow_delete' => true
            ])
//            ->add('choices', CollectionType::class, [
//                'label' => 'Seçenekler',
//                'allow_add' => true,
//                'allow_delete' => true,
//                'entry_type' => 'App\\Form\\OptionType'
//            ])
            ->add('save', SubmitType::class, ['label' => 'Kaydet'])
//            ->addEventSubscriber(new ChoiceQuestionListener())
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\\Entity\\ChoiceQuestion',
        ]);
    }
}