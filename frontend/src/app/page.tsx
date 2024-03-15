'use client';
import React, { useEffect, useState, useCallback } from 'react';
import axios from 'axios';
import SearchForm from '../components/SearchForm';
import DataTable from '../components/DataTable';

interface Repository {
  id: number;
  name: string;
  full_name: string;
  html_url: string;
  language: string;
  updated_at: string;
  pushed_at: string;
  stargazers_count: number;
}

interface FetchParams {
  search: string;
  sort: 'name' | 'popularity' | 'activity';
  order: 'asc' | 'desc';
  per_page: number;
  page: number;
}

const defaultParams: FetchParams = {
  search: '',
  sort: 'name',
  order: 'asc',
  per_page: 50,
  page: 1,
};

export default function Home() {
  const [repositories, setRepositories] = useState<Repository[]>([]);
  const [total, setTotal] = useState(0);
  const [isLoading, setIsLoading] = useState(false);
  const [fetchParams, setFetchParams] = useState<FetchParams>(defaultParams);

  const fetchRepositories = useCallback(async (params: FetchParams) => {
    setIsLoading(true);
    try {
      const response = await axios.get(`http://localhost:8000/api/repos`, {
        params,
      });
      setRepositories(response.data.data);
      setTotal(response.data.total);
    } catch (error) {
      console.error('Failed to fetch repositories:', error);
    }
    setIsLoading(false);
  }, []);

  useEffect(() => {
    fetchRepositories(fetchParams);
  }, [fetchParams, fetchRepositories]);

  const handleSearch = (query: string) => {
    const newParams = { ...fetchParams, search: query, page: 1 };
    setFetchParams(newParams);
  };

  const handlePageChange = (selectedItem: { selected: number }) => {
    const newPage = selectedItem.selected + 1;
    if (newPage !== fetchParams.page) {
      setFetchParams({ ...fetchParams, page: newPage });
    }
  };

  const handleSort = (sortField: 'name' | 'popularity' | 'activity') => {
    const newOrder = fetchParams.order === 'asc' ? 'desc' : 'asc';
    if (sortField !== fetchParams.sort || newOrder !== fetchParams.order) {
      setFetchParams({ ...fetchParams, sort: sortField, order: newOrder });
    }
  };

  if (isLoading) {
    return <div>Loading...</div>;
  }

  return (
    <div className="p-4">
      <SearchForm
        onSearch={handleSearch}
        disabled={isLoading}
        query={fetchParams.search}
      />
      <DataTable
        data={repositories}
        total={total}
        perPage={fetchParams.per_page}
        currentPage={fetchParams.page}
        sort={fetchParams.sort}
        order={fetchParams.order}
        onSort={handleSort}
        onPageChange={handlePageChange}
      />
    </div>
  );
}
