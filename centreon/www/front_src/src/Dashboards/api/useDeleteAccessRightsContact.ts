import {
  MutateOptions,
  UseMutationResult,
  useQueryClient
} from '@tanstack/react-query';

import { Method, ResponseError, useMutationQuery } from '@centreon/ui';

import { DeleteAccessRightDto, resource } from './models';
import { getDashboardAccessRightsContactEndpoint } from './endpoints';

type UseDeleteAccessRightsContact<
  TData extends null = null,
  TVariables extends DeleteAccessRightDto = DeleteAccessRightDto,
  TError = ResponseError
> = {
  mutate: (
    variables: TVariables,
    options?: MutateOptions<TData, TError, TVariables>
  ) => Promise<TData | TError>;
} & Omit<
  UseMutationResult<TData | TError, TError, TVariables>,
  'mutate' | 'mutateAsync'
>;

const useDeleteAccessRightsContact = (): UseDeleteAccessRightsContact => {
  const {
    mutateAsync,
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    mutate: omittedMutate,
    ...mutationData
  } = useMutationQuery<object, { dashboardId; id }>({
    getEndpoint: ({ id, dashboardId }) =>
      getDashboardAccessRightsContactEndpoint(dashboardId, id),
    method: Method.DELETE,
    mutationKey: [resource.dashboardAccessRightsContacts, 'delete']
  });

  const queryClient = useQueryClient();
  const invalidateQueries = ({ _meta }): Promise<void> =>
    queryClient.invalidateQueries({
      queryKey: [resource.dashboardAccessRightsContacts, _meta?.dashboardId]
    });

  const mutate = async (
    variables: DeleteAccessRightDto,
    options?: MutateOptions<object, unknown, DeleteAccessRightDto>
  ): Promise<object | ResponseError> => {
    const { onSettled, ...restOptions } = options || {};

    const onSettledWithInvalidateQueries = (
      data: undefined,
      error: ResponseError | null,
      vars: DeleteAccessRightDto
    ): void => {
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      invalidateQueries(vars as any);
      onSettled?.(data, error, vars, undefined);
    };

    /* eslint-disable @typescript-eslint/no-explicit-any */
    const { id, dashboardId } = variables as any;
    /* eslint-enable @typescript-eslint/no-explicit-any */

    return mutateAsync(
      {
        _meta: {
          dashboardId,
          id
        }
      },
      {
        onSettled: onSettledWithInvalidateQueries,
        ...restOptions
      }
    );
  };

  return {
    mutate,
    ...mutationData
  };
};

export { useDeleteAccessRightsContact };
