<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 21.06.18
 * Time: 09:17
 */

namespace Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class UserType.
 **/
class UserType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'login',
            TextType::class,
            [
                'label' => 'label.login',
                'required' => true,
                'attr' => [
                    'max_length' => 16,

                ],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(
                        [
                            'min' => 5,
                            'max' => 16,
                        ]
                    ),
                ],
            ]
        );
        $builder->add(
            'password',
            PasswordType::class,
            [
                'label' => 'label.password',
                'required' => true,
                'attr' => [
                    'max_length' => 32,

                ],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(
                        [
                            'min' => 4,
                            'max' => 32,
                        ]
                    ),
                ],
            ]
        );

        $builder->add(
            'name',
            TextType::class,
            [
                'label' => 'label.user_name',
                'required' => false,
                'attr' => [
                    'max_length' => 30,
                ],
            ]
        );

        $builder->add(
            'age',
            TextType::class,
            [
                'label' => 'label.user_age',
                'required' => false,
                'attr' => [
                    'max_length' => 3,
                ],
            ]
        );

        $builder->add(
            'gender',
            TextType::class,
            [
                'label' => 'label.user_gender',
                'required' => false,
                'attr' => [
                    'max_length' => 1,
                ],
            ]
        );

        $builder->add(
            'email',
            TextType::class,
            [
                'label' => 'label.user_email',
                'required' => false,
                'attr' => [
                    'max_length' => 35,
                ],
            ]
        );

        $builder->add(
            'description',
            TextType::class,
            [
                'label' => 'label.user_description',
                'required' => false,
                'attr' => [
                    'max_length' => 300,
                ],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'user_type';
    }
}
