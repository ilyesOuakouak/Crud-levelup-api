<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ObjectNormalizer
{
    private NormalizerInterface $normalizer;
    public function __construct(NormalizerInterface $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    public function getNormalizedObject($object, $status = null, array $groups = []): JsonResponse
    {
        $context = new ObjectNormalizerContextBuilder()
            ->withGroups($groups)
            ->toArray()
        ;

        return new JsonResponse([
            'normalizedObject' => $this->normalizer->normalize($object, 'array', $context),
            'status' => $status
        ]);
    }

}
