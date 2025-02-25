import { useTranslation } from 'react-i18next';

import { Box, Typography } from '@mui/material';

import useStyle from './PageHeader.styles';
import { labelResourceAccessRules } from './translatedLabels';
import Filter from './Filter';

const Title = (): JSX.Element => {
  const { classes } = useStyle();
  const { t } = useTranslation();

  return (
    <Typography className={classes.title} variant="h5">
      {t(labelResourceAccessRules)}
      <div id="ceip_badge" />
    </Typography>
  );
};

const PageHeader = (): JSX.Element => {
  const { classes } = useStyle();

  return (
    <Box className={classes.box}>
      <Title />
      <Filter />
    </Box>
  );
};

export default PageHeader;
