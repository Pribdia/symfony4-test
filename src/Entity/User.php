<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity("email",message="Cette adresse mail est déjà utilisée.")
 */
class User implements UserInterface,\Serializable
{


    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(
     *      max=250,
     *      maxMessage="Le prénom ne peux pas dépasser 250 caractères"
     * )
     * @Assert\NotBlank(
     *      message="Le prénom ne peux pas être vide."
     * )
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(
     *      max=250,
     *      maxMessage="Le nom ne peux pas dépasser 250 caractères"
     * )
     * @Assert\NotBlank(
     *      message="Le nom ne peux pas être vide."
     * )
     */
    private $lastName;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Email(
     *      message = "L'adresse mail '{{ value }}' n'est pas valide."
     * )
     */
    private $email;

    /**
     * @ORM\Column(type="date")
     * @Assert\NotBlank(message="La date de naissance est obligatoire")
     */
    private $birthDay;

    /**
     * @ORM\Column(type="boolean")
     */
    private $admin;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Activity", mappedBy="users")
     */
    private $activities;

    /**
     * @ORM\Column(type="boolean")
     */
    private $active;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(
     *      max=250,
     *      maxMessage="Le mot de passe ne peux pas dépasser 250 caractères",
     *      min=6,
     *      minMessage="Le mot de passe doit contenir au minimum de 6 caractères",
     * )
     * @Assert\NotBlank(
     *      message="Le mot de passe ne peux pas être vide."
     * )
     */
    private $password;

    public function __construct()
    {
        $this->active = false;
        $this->admin = false;
        $this->activities = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getBirthDay(): ?\DateTimeInterface
    {
        return $this->birthDay;
    }

    public function setBirthDay(\DateTimeInterface $birthDay): self
    {
        $this->birthDay = $birthDay;

        return $this;
    }

    public function getAdmin(): ?bool
    {
        return $this->admin;
    }

    public function setAdmin(bool $admin): self
    {
        $this->admin = $admin;

        return $this;
    }

    public function ifHaveActivity(Activity $activity) :bool
    {
        $activities = $this->getActivities();
        $activityId = $activity->getId();

        foreach ($activities as $activity)
        {
            if($activity->getId() == $activityId)
            {
                return true;
            }
        }
        return false;
    }

    /**
     * @return Collection|Activity[]
     */
    public function getActivities(): Collection
    {
        return $this->activities;
    }

    public function addActivity(Activity $activity): self
    {
        if (!$this->activities->contains($activity)) {
            $this->activities[] = $activity;
            $activity->addUser($this);
        }

        return $this;
    }

    public function removeActivity(Activity $activity): self
    {
        if ($this->activities->contains($activity)) {
            $this->activities->removeElement($activity);
            $activity->removeUser($this);
        }

        return $this;
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function serialize()
    {
        return serialize([
            $this->id,
            $this->firstName,
            $this->lastName,
            $this->email,
            $this->password,
            $this->admin,
            $this->active,
            $this->birthDay,
            $this->activities
        ]);
    }

    public function unserialize($serialized)
    {
        list(
            $this->id,
            $this->firstName,
            $this->lastName,
            $this->email,
            $this->password,
            $this->admin,
            $this->active,
            $this->birthDay,
            $this->activities
            ) = unserialize($serialized, ['allowed_classes' => false]);
    }

    public function getRoles()
    {
        if ($this->admin)
        {
            return ['ROLE_USER','ROLE_ADMIN','ROLE_ACTIVE_USER'];
        }
        elseif ($this->active)
        {
            return ['ROLE_USER','ROLE_ACTIVE_USER'];
        }
        return ['ROLE_USER'];
    }

    public function getSalt()
    {
        return null;
    }

    public function getUsername()
    {
        $this->getEmail();
    }

    public function eraseCredentials()
    {
    }
}
