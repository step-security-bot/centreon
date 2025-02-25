import { useRef } from 'react';

import { useTranslation } from 'react-i18next';
import { useSetAtom } from 'jotai';

import debounce from '@mui/utils/debounce';

import { SearchField } from '@centreon/ui';

import { searchAtom } from '../atom';
import { labelSearch } from '../translatedLabels';

import { useActionsStyles } from './useActionsStyles';

const Filter = (): JSX.Element => {
  const { classes } = useActionsStyles();

  const { t } = useTranslation();

  const setSearchVAlue = useSetAtom(searchAtom);

  const searchDebounced = useRef(
    debounce<(search) => void>((debouncedSearch): void => {
      setSearchVAlue(debouncedSearch);
    }, 500)
  );

  const onChange = ({ target }): void => {
    searchDebounced.current(target.value);
  };

  return (
    <SearchField
      debounced
      fullWidth
      className={classes.filter}
      dataTestId={t(labelSearch)}
      placeholder={t(labelSearch)}
      onChange={onChange}
    />
  );
};

export default Filter;
