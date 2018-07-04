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
use Validator\Constraints as CustomAssert;

/**
 * Class AccountType.
 **/
class AccountType extends AbstractType
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
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'account_type';
    }
}
