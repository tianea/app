<?php
/**
 * User form.
 *
 * @copyright (c) 2018 Monika Kwiecień
 *
 * @link http://cis.wzks.uj.edu.pl/~15_kwiecien/web/surveys/
 */

namespace Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class UserType.
 **/
class UserType extends AbstractType
{
    /**
     * BuildForm function.
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
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
                    new Assert\NotBlank(
                        [
                            'groups' => ['user-default'],
                        ]
                    ),
                    new Assert\Length(
                        [
                            'groups' => ['user-default'],
                            'min' => 5,
                            'max' => 16,
                        ]
                    ),
                ],
            ]
        );

        $builder->add('password', RepeatedType::class, array(
            'type' => PasswordType::class,
            'options' => array('attr' => array('class' => 'password-field')),
            'required' => true,
            'attr' => [
                'max_length' => 32,

            ],
            'constraints' => [
                new Assert\NotBlank(
                    [
                        'groups' => ['user-default'],
                    ]
                ),
                new Assert\Length(
                    [
                        'groups' => ['user-default'],
                        'min' => 6,
                        'max' => 32,
                    ]
                ),
            ],
            'first_options'  => array('label' => 'label.password'),
            'second_options' => array('label' => 'label.repeat_password'),
        ));

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
            ChoiceType::class,
            [
                'label' => 'label.user_gender',
                'required' => false,
                'choices'  => [
                    'label.female' => 'K',
                    'label.male' => 'M',
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
     * Method configureOptions.
     *
     * @param OptionsResolver $resolver
     *
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'validation_groups' => 'user-default',
                'user_repository' => null,
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