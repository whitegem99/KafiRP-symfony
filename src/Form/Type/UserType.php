<?php declare(strict_types=1);

namespace App\Form\Type;

use App\Enum\RoleType;
use App\Helper\AccessHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    /**
     * @var AccessHelper
     */
    private $accessHelper;

    public function __construct(AccessHelper $accessHelper)
    {
        $this->accessHelper = $accessHelper;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, ['label' => 'Kullanıcı Adı'])
            ->add('password', PasswordType::class, ['label' => 'Parola', 'required' => $options['require_password']])
            ->add('roles', ChoiceType::class, [
                'label' => 'Yetkiler',
                'label_attr' => [
                    'class' => 'c-choice__label',
                ],
                'attr' => [
                    'class' => 'c-choice__input'
                ],
                'expanded' => true,
                'multiple' => true,
                'choices' => $this->accessHelper->getFormList()
            ])
            ->add('email', TextType::class, ['label' => 'E-Posta Adresi'])
            ->add('save', SubmitType::class, ['label' => 'Kaydet'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'require_password' => true,
        ]);

        $resolver->setAllowedTypes('require_password', 'bool');
    }
}