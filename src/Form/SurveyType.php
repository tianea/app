<?php
/**
 * Survey type.
 */
namespace Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class TagType.
 */
class SurveyType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'name',
            TextType::class,
            [
                'label' => 'label.name',
                'required' => true,
                'attr' => [
                    'max_length' => 180,
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(
                        [
                            'min' => 3,
                            'max' => 180,
                        ]
                    ),
                ],
            ]
        );

        $builder->add(
            'description',
            TextType::class,
            [
                'label' => 'label.description',
                'required' => false,
                'attr' => [
                    'max_length' => 1500,
                ]
            ]
        );

        $builder->add(
            'question',
            TextType::class,
            [
                'label' => 'label.description',
                'required' => true,
                'attr' => [
                    'max_length' => 450,
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(
                        [
                            'min' => 5,
                            'max' => 450,
                        ]
                    ),
                ]
            ]
        );

    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'survey_type';
    }
}