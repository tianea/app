<?php
/**
 * Password form.
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
use Validator\Constraints as CustomAssert;

/**
 * Class PasswdType.
 **/
class PasswdType extends AbstractType
{
    /**
     * BuildForm function.
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
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
                        'groups' => ['passwd-default'],
                    ]
                ),
                new Assert\Length(
                    [
                        'groups' => ['passwd-default'],
                        'min' => 6,
                        'max' => 32,
                    ]
                ),
            ],
            'first_options' => array('label' => 'label.password'),
            'second_options' => array('label' => 'label.repeat_password'),
        ));
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
                'validation_groups' => 'passwd-default',
                'user_repository' => null,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'passwd_type';
    }
}
