<?php
/**
 * Change role form.
 *
 * @copyright (c) 2018 Monika Kwiecień
 *
 * @link http://cis.wzks.uj.edu.pl/~15_kwiecien/web/surveys/
 */

namespace Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ChangeRoleType.
 *
 */
class ChangeRoleType extends AbstractType
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
            'role_id',
            ChoiceType::class,
            [
                'label' => 'label.user_role',
                'required' => true,
                'choices'  => [
                    'label.admin' => '1',
                    'label.user' => '2',
                ],
                'constraints' => [
                    new Assert\NotBlank(
                        ['groups' => ['change-role-default']]
                    ),
                    new Assert\Choice(
                        [
                            'groups' => ['change-role-default'],
                            'multiple' => false,
                            'choices' => ['1', '2'],
                        ]
                    ),
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
                'validation_groups' => 'change-role-default',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'change_role_type';
    }
}