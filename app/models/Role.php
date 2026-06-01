<?php

/**
 * Role Model
 *
 * Handles all database operations related to the roles table.
 */

class Role extends Model
{
    protected string $table = 'roles';

    /**
     * Return all roles as [id => name] for select boxes.
     */
    public function allForSelect(): array
    {
        $rows = $this->all('id', 'ASC');
        $map  = [];
        foreach ($rows as $row) {
            $map[$row['id']] = $row['name'];
        }
        return $map;
    }

    /**
     * Find a role by its slug.
     */
    public function findBySlug(string $slug): array|false
    {
        return $this->findOneBy('slug', $slug);
    }

    /**
     * Return a map of [slug => id] for fast lookups during bulk import.
     */
    public function allSlugsToIds(): array
    {
        $rows = $this->all();
        $map = [];
        foreach ($rows as $row) {
            $map[$row['slug']] = (int) $row['id'];
        }
        return $map;
    }
}
