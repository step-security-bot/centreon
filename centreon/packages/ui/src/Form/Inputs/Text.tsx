import { ChangeEvent, useCallback, useState } from 'react';

import { useTranslation } from 'react-i18next';
import { useFormikContext, FormikValues } from 'formik';
import {
  equals,
  gt,
  isEmpty,
  not,
  path,
  split,
  type as variableType
} from 'ramda';

import { InputAdornment } from '@mui/material';

import { TextField, useMemoComponent } from '../..';

import PasswordEndAdornment from './PasswordEndAdornment';
import { InputPropsWithoutGroup, InputType } from './models';

const Text = ({
  dataTestId,
  label,
  fieldName,
  type,
  required,
  getDisabled,
  getRequired,
  change,
  additionalMemoProps,
  text
}: InputPropsWithoutGroup): JSX.Element => {
  const { t } = useTranslation();

  const [isVisible, setIsVisible] = useState(false);

  const { values, setFieldValue, touched, errors, handleBlur } =
    useFormikContext<FormikValues>();

  const fieldNamePath = split('.', fieldName);

  const changeText = (event: ChangeEvent<HTMLInputElement>): void => {
    const { value } = event.target;
    if (change) {
      change({ setFieldValue, value });

      return;
    }

    const formattedValue =
      equals(text?.type, 'number') && !isEmpty(value)
        ? parseInt(value, 10)
        : value;

    setFieldValue(fieldName, formattedValue);
  };

  const changeVisibility = (): void => {
    setIsVisible((currentIsVisible) => !currentIsVisible);
  };

  const value = path(fieldNamePath, values);

  const error = path(fieldNamePath, touched)
    ? path(fieldNamePath, errors)
    : undefined;

  const EndAdornment = useCallback((): JSX.Element | null => {
    if (equals(type, InputType.Password)) {
      return (
        <PasswordEndAdornment
          changeVisibility={changeVisibility}
          isVisible={isVisible}
        />
      );
    }

    if (text?.endAdornment) {
      return (
        <InputAdornment position="end">{text?.endAdornment}</InputAdornment>
      );
    }

    return null;
  }, [isVisible]);

  const getInputType = (): string => {
    if (text?.type) {
      return text.type;
    }

    return equals(type, InputType.Password) && not(isVisible)
      ? 'password'
      : 'text';
  };

  const disabled = getDisabled?.(values) || false;
  const isRequired = required || getRequired?.(values) || false;

  const isMultiline =
    equals(variableType(text?.multilineRows), 'Number') &&
    gt(text?.multilineRows || 0, 0);
  const rows = isMultiline ? text?.multilineRows : undefined;

  return useMemoComponent({
    Component: (
      <TextField
        fullWidth
        EndAdornment={EndAdornment}
        ariaLabel={t(label) || ''}
        dataTestId={dataTestId || ''}
        disabled={disabled}
        error={error as string | undefined}
        label={t(label)}
        multiline={isMultiline}
        placeholder={text?.placeholder}
        required={isRequired}
        rows={rows}
        type={getInputType()}
        value={value || ''}
        onBlur={handleBlur(fieldName)}
        onChange={changeText}
      />
    ),
    memoProps: [
      error,
      value,
      isVisible,
      disabled,
      isRequired,
      additionalMemoProps
    ]
  });
};

export default Text;
