<?php


declare(strict_types=1);

namespace KED\Module\User\Services;

class Guest implements UserInterface
{
    public function getId()
    {
        null;
    }

    public function getGroupId()
    {
        null;
    }

    public function getEmail()
    {
        null;
    }

    public function getOrders()
    {
        null;
    }

    public function getGender()
    {
        null;
    }

    public function getFirstname()
    {
        return null;
    }

    public function getLastname()
    {
        return null;
    }

    public function getDob()
    {
        return null;
    }

    public function getCreatedAt()
    {
        return null;
    }

    public function getUpdatedAt()
    {
        return null;
    }

    public function getStatus()
    {
        return null;
    }
}