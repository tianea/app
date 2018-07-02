<?php
/**
 * Answer form.
 *
 * @copyright (c) 2018 Monika Kwiecień
 *
 * @link http://cis.wzks.uj.edu.pl/~15_kwiecien/web/surveys/
 */

namespace Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class AnswerType
 */
class AnswerType extends AbstractType
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
            'answer',
            TextType::class,
            [
                'label' => 'label.answer',
                'required' => true,
                'attr' => [
                    'max_length' => 300,
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(
                        [
                            'min' => 1,
                            'max' => 300,
                        ]
                    ),
                ],
            ]
        );
    }

    /**
     * GetBlockPrefix function.
     *
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'answer_type';
    }
}
